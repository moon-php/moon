<?php

declare(strict_types=1);

namespace Moon\Moon;

use Exception;
use InvalidArgumentException;
use Moon\Moon\Collection\PipelineCollectionInterface;
use Moon\Moon\Container\ContainerWrapper;
use Moon\Moon\Container\ContainerWrapperInterface;
use Moon\Moon\Exception\InvalidArgumentException as MoonInvalidArgumentException;
use Moon\Moon\Exception\UnprocessableStageException;
use Moon\Moon\Matchable\MatchableInterface;
use Moon\Moon\Pipeline\AbstractPipeline;
use Moon\Moon\Pipeline\HttpPipeline;
use Moon\Moon\Pipeline\PipelineInterface;
use Moon\Moon\Processor\ProcessorInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Throwable;
use function array_merge;
use function header;
use function http_response_code;

class App extends AbstractPipeline implements PipelineInterface
{
    /**
     * @var ContainerWrapperInterface $containerWrapper
     */
    private $containerWrapper;

    /**
     * App constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->containerWrapper = new ContainerWrapper($container);
    }

    /**
     * Run the web application, and print a Response
     *
     * @param PipelineCollectionInterface $pipelines
     *
     * @return void
     *
     * @throws ContainerExceptionInterface
     * @throws MoonInvalidArgumentException
     * @throws NotFoundExceptionInterface
     * @throws RuntimeException
     */
    public function run(PipelineCollectionInterface $pipelines): void
    {
        $request = $this->containerWrapper->request();
        $response = $this->containerWrapper->response();
        $processor = $this->containerWrapper->processor();
        $exceptionHandler = $this->containerWrapper->exceptionHandler();
        $throwableHandler = $this->containerWrapper->throwableHandler();
        $notFoundHandler = $this->containerWrapper->notFoundHandler();
        $methodNotAllowed = $this->containerWrapper->methodNotAllowed();
        $matchableRequest = $this->containerWrapper->matchableRequest();

        try {
            // If a pipeline match print the response and return
            if ($handledResponse = $this->handlePipeline($pipelines, $matchableRequest, $processor, $response)) {
                $this->sendResponse($handledResponse);

                return;
            }

            // If a route pattern matched but the http verbs was different, print a '405 response' and return
            if ($matchableRequest->isPatternMatched()) {
                $this->sendResponse($methodNotAllowed($request, $response));

                return;
            }

            // If no route pattern matched, print a '404 response' and return
            $this->sendResponse($notFoundHandler($request, $response));

            return;
        } catch (Exception $e) {
            $this->sendResponse($exceptionHandler($e, $request, $response));
        } catch (Throwable $e) {
            $this->sendResponse($throwableHandler($e, $request, $response));
        }
    }

    /**
     * Process the pipelines and return a ResponseInterface if one of this match
     *
     * @param PipelineCollectionInterface $pipelines
     * @param MatchableInterface $matchableRequest
     * @param ProcessorInterface $processor
     * @param ResponseInterface $response
     *
     * @return ResponseInterface|void
     *
     * @throws UnprocessableStageException
     * @throws ContainerExceptionInterface
     * @throws InvalidArgumentException
     * @throws MoonInvalidArgumentException
     * @throws NotFoundExceptionInterface
     * @throws RuntimeException
     */
    private function handlePipeline(
        PipelineCollectionInterface $pipelines,
        MatchableInterface $matchableRequest,
        ProcessorInterface $processor,
        ResponseInterface $response
    ):?ResponseInterface
    {
        /** @var HttpPipeline $pipeline */
        foreach ($pipelines as $pipeline) {
            if ($pipeline->matchBy($matchableRequest)) {

                $stages = array_merge($this->stages(), $pipeline->stages());
                $pipelineResponse = $processor->processStages($stages, $matchableRequest->requestWithAddedAttributes());

                if ($pipelineResponse instanceof ResponseInterface) {

                    return $pipelineResponse;
                }

                $stream = $this->containerWrapper->stream();
                $stream->write($pipelineResponse);

                return $response->withBody($stream);
            }
        }

        return null;
    }

    /**
     * Send headers and body to the client
     *
     * @param ResponseInterface $response
     *
     * @return void
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws RuntimeException
     */
    protected function sendResponse(ResponseInterface $response): void
    {
        // Send all the headers
        http_response_code($response->getStatusCode());
        foreach ($response->getHeaders() as $headerName => $headerValues) {
            /** @var string[] $headerValues */
            foreach ($headerValues as $headerValue) {
                header("$headerName: $headerValue", false);
            }
        }

        // Get the body, rewind it if possible
        $body = $response->getBody();
        if ($body->isSeekable()) {
            $body->rewind();
        }

        // If the body is not readable do not send any body to the client
        if (!$body->isReadable()) {

            return;
        }

        // Send the body (by chunk if specified in the container)
        if ($length = $this->containerWrapper->streamReadLength()) {
            while (!$body->eof()) {
                echo $body->read($length);
            }

            return;
        }

        echo $body->__toString();
    }
}