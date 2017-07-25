<?php

declare(strict_types=1);

namespace Moon\Moon;

use Exception;
use InvalidArgumentException;
use Moon\Moon\Handler\Error\ErrorHandlerInterface;
use Moon\Moon\Handler\Error\ExceptionHandler;
use Moon\Moon\Handler\Error\ThrowableHandler;
use Moon\Moon\Handler\InvalidRequest\InvalidRequestInterface;
use Moon\Moon\Handler\InvalidRequest\MethodNotAllowedHandler;
use Moon\Moon\Handler\InvalidRequest\NotFoundHandler;
use Moon\Moon\Collection\PipelineCollectionInterface;
use Moon\Moon\Exception\InvalidArgumentException as MoonInvalidArgumentException;
use Moon\Moon\Exception\UnprocessableStageException;
use Moon\Moon\Matchable\MatchableInterface;
use Moon\Moon\Matchable\RequestMatchable;
use Moon\Moon\Pipeline\AbstractPipeline;
use Moon\Moon\Pipeline\HttpPipeline;
use Moon\Moon\Pipeline\PipelineInterface;
use Moon\Moon\Processor\ProcessorInterface;
use Moon\Moon\Processor\WebProcessor;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;
use Throwable;

class App extends AbstractPipeline implements PipelineInterface
{
    /**
     * @var ContainerInterface $container
     */
    private $container;

    /**
     * App constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
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
        $request = $this->getContainerEntryOrFail('moon.request', ServerRequestInterface::class);
        $response = $this->getContainerEntryOrFail('moon.response', ResponseInterface::class);
        $processor = $this->getContainerEntryOrFail('moon.webProcessor', ProcessorInterface::class, new WebProcessor($this->container));
        $exceptionHandler = $this->getContainerEntryOrFail('moon.exceptionHandler', ErrorHandlerInterface::class, new ExceptionHandler());
        $throwableHandler = $this->getContainerEntryOrFail('moon.throwableHandler', ErrorHandlerInterface::class, new ThrowableHandler());
        $notFoundHandler = $this->getContainerEntryOrFail('moon.notFoundHandler', InvalidRequestInterface::class, new NotFoundHandler());
        $methodNotAllowed = $this->getContainerEntryOrFail('moon.methodNotAllowedHandler', InvalidRequestInterface::class, new MethodNotAllowedHandler());
        $matchableRequest = new RequestMatchable($request);

        try {
            // If a pipeline match print the response and return
            if ($response = $this->handlePipeline($pipelines, $matchableRequest, $processor, $response)) {
                $this->sendResponse($response);

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

                $pipelineResponse = $processor->processStages(array_merge($this->stages(), $pipeline->stages()), $matchableRequest->requestWithAddedAttributes());

                if ($pipelineResponse instanceof ResponseInterface) {

                    return $pipelineResponse;
                }

                $stream = $this->container->get('moon.stream');
                if (!$stream instanceof StreamInterface) {
                    throw new MoonInvalidArgumentException('Stream must be a valid ' . StreamInterface::class . ' instance');
                }

                $stream->write($pipelineResponse);

                return $response->withBody($stream);
            }
        }
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
        if ($this->container->has('moon.chunkSize')) {
            $chunk = $this->container->get('moon.chunkSize');
            while (!$body->eof()) {
                echo $body->read($chunk);
            }

            return;
        }

        echo $body->__toString();
    }

    /**
     * Return an instance by the container or a default type
     *
     * @param string $entry
     * @param string $class
     * @param null $default
     *
     * @return mixed|null
     *
     * @throws ContainerExceptionInterface
     * @throws MoonInvalidArgumentException
     * @throws NotFoundExceptionInterface
     */
    private function getContainerEntryOrFail(string $entry, string $class, $default = null)
    {
        $object = $this->container->has($entry) ? $this->container->get($entry) : $default;

        if (!$object instanceof $class) {
            throw new MoonInvalidArgumentException("$entry must be a valid $class instance");
        }

        return $object;
    }
}