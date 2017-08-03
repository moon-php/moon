<?php

declare(strict_types=1);

namespace Moon\Moon;

use Fig\Http\Message\StatusCodeInterface;
use InvalidArgumentException;
use Moon\Moon\Collection\PipelineCollectionInterface;
use Moon\Moon\Exception\UnprocessableStageException;
use Moon\Moon\Handler\ErrorHandlerInterface;
use Moon\Moon\Handler\InvalidRequestHandlerInterface;
use Moon\Moon\Matchable\MatchableRequestInterface;
use Moon\Moon\Pipeline\AbstractPipeline;
use Moon\Moon\Pipeline\HttpPipeline;
use Moon\Moon\Pipeline\PipelineInterface;
use Moon\Moon\Processor\ProcessorInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Throwable;
use function array_merge;
use function header;
use function http_response_code;

class App extends AbstractPipeline implements PipelineInterface
{
    /**
     * @var ServerRequestInterface
     */
    private $request;
    /**
     * @var ResponseInterface
     */
    private $response;
    /**
     * @var ProcessorInterface
     */
    private $processor;
    /**
     * @var MatchableRequestInterface
     */
    private $matchableRequest;
    /**
     * @var ErrorHandlerInterface
     */
    private $errorHandler;
    /**
     * @var InvalidRequestHandlerInterface
     */
    private $invalidRequestHandler;
    /**
     * @var int|null
     */
    private $streamReadLength;

    /**
     * App constructor.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param ProcessorInterface $processor
     * @param MatchableRequestInterface $matchableRequest
     * @param ErrorHandlerInterface $errorHandler
     * @param InvalidRequestHandlerInterface $invalidRequestHandler
     * @param int|null $streamReadLength
     */
    public function __construct(
        ServerRequestInterface $request,
        ResponseInterface $response,
        ProcessorInterface $processor,
        MatchableRequestInterface $matchableRequest,
        ErrorHandlerInterface $errorHandler,
        InvalidRequestHandlerInterface $invalidRequestHandler,
        int $streamReadLength = null
    )
    {
        $this->request = $request;
        $this->response = $response;
        $this->processor = $processor;
        $this->matchableRequest = $matchableRequest;
        $this->errorHandler = $errorHandler;
        $this->invalidRequestHandler = $invalidRequestHandler;
        $this->streamReadLength = $streamReadLength;
    }

    /**
     * Run the web application, and print a Response
     *
     * @param PipelineCollectionInterface $pipelines
     *
     * @return void
     *
     * @throws RuntimeException
     */
    public function run(PipelineCollectionInterface $pipelines): void
    {
        try {
            // If a pipeline match print the response and return
            if ($handledResponse = $this->handlePipeline($pipelines)) {
                $this->sendResponse($handledResponse);

                return;
            }

            // If a route pattern matched but the http verbs was different, print a '405 response' and return
            // If no route pattern matched, print a '404 response' and return
            $statusCode = $this->matchableRequest->isPatternMatched() ? StatusCodeInterface::STATUS_METHOD_NOT_ALLOWED : StatusCodeInterface::STATUS_NOT_FOUND;
            $this->sendResponse($this->invalidRequestHandler->__invoke($this->matchableRequest->request(), $this->response->withStatus($statusCode)));

        } catch (Throwable $e) {
            $this->sendResponse($this->errorHandler->__invoke($e, $this->request, $this->response));
        }
    }

    /**
     * Process the pipelines and return a ResponseInterface if one of this match
     *
     * @param PipelineCollectionInterface $pipelines
     *
     * @return null|ResponseInterface
     *
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws UnprocessableStageException
     */
    private function handlePipeline(PipelineCollectionInterface $pipelines):?ResponseInterface
    {
        /** @var HttpPipeline $pipeline */
        foreach ($pipelines as $pipeline) {
            if ($pipeline->matchBy($this->matchableRequest)) {

                $stages = array_merge($this->stages(), $pipeline->stages());
                $pipelineResponse = $this->processor->processStages($stages, $this->matchableRequest->request());

                if ($pipelineResponse instanceof ResponseInterface) {

                    return $pipelineResponse;
                }

                $body = $this->response->getBody();
                $body->write($pipelineResponse);

                return $this->response->withBody($body);
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
        if ($this->streamReadLength !== null) {
            while (!$body->eof()) {
                echo $body->read($this->streamReadLength);
            }

            return;
        }

        echo $body->__toString();
    }
}