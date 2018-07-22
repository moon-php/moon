<?php

declare(strict_types=1);

namespace Moon\Moon;

use Fig\Http\Message\StatusCodeInterface;
use Moon\Moon\Collection\MatchablePipelineCollectionInterface;
use Moon\Moon\Exception\UnprocessableStageException;
use Moon\Moon\Handler\ErrorHandlerInterface;
use Moon\Moon\Handler\InvalidRequestHandlerInterface;
use Moon\Moon\Matchable\MatchableRequestInterface;
use Moon\Moon\Pipeline\AbstractPipeline;
use Moon\Moon\Pipeline\MatchablePipelineInterface;
use Moon\Moon\Pipeline\PipelineInterface;
use Moon\Moon\Processor\ProcessorInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Throwable;

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

    public function __construct(
        ServerRequestInterface $request,
        ResponseInterface $response,
        ProcessorInterface $processor,
        MatchableRequestInterface $matchableRequest,
        ErrorHandlerInterface $errorHandler,
        InvalidRequestHandlerInterface $invalidRequestHandler,
        int $streamReadLength = null
    ) {
        $this->request = $request;
        $this->response = $response;
        $this->processor = $processor;
        $this->matchableRequest = $matchableRequest;
        $this->errorHandler = $errorHandler;
        $this->invalidRequestHandler = $invalidRequestHandler;
        $this->streamReadLength = $streamReadLength;
    }

    /**
     * Run the web application, and print a Response.
     *
     * @throws RuntimeException
     */
    public function run(MatchablePipelineCollectionInterface $pipelines): void
    {
        try {
            // If a pipeline match print the response and return
            if ($handledResponse = $this->handlePipeline($pipelines)) {
                $this->sendResponse($handledResponse);

                return;
            }

            // If a route pattern matched but the http verbs was different, print a '405 response'
            // If no route pattern matched, print a '404 response'
            $statusCode = $this->matchableRequest->isPatternMatched() ? StatusCodeInterface::STATUS_METHOD_NOT_ALLOWED : StatusCodeInterface::STATUS_NOT_FOUND;
            $this->sendResponse($this->invalidRequestHandler->__invoke($this->matchableRequest->request(), $this->response->withStatus($statusCode)));
        } catch (Throwable $e) {
            $this->sendResponse($this->errorHandler->__invoke($e, $this->request, $this->response));
        }
    }

    /**
     * Process the pipelines and return a ResponseInterface if one of this match.
     *
     * @throws UnprocessableStageException
     */
    private function handlePipeline(MatchablePipelineCollectionInterface $pipelines): ?ResponseInterface
    {
        /** @var MatchablePipelineInterface $pipeline */
        foreach ($pipelines as $pipeline) {
            if ($pipeline->matchBy($this->matchableRequest)) {
                $stages = \array_merge($this->stages(), $pipeline->stages());
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
     * Send headers and body to the client.
     *
     * @throws RuntimeException
     */
    protected function sendResponse(ResponseInterface $response): void
    {
        // Send all the headers
        \http_response_code($response->getStatusCode());
        foreach ($response->getHeaders() as $headerName => $headerValues) {
            /** @var string[] $headerValues */
            foreach ($headerValues as $headerValue) {
                \header("$headerName: $headerValue", false);
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

        // Send the body (by chunk if specified in the container using streamReadLength value)
        if (null === $this->streamReadLength) {
            echo $body->__toString();

            return;
        }

        while (!$body->eof()) {
            echo $body->read($this->streamReadLength);
        }
    }
}
