<?php

declare(strict_types=1);

namespace Moon\Moon;

use Exception;
use InvalidArgumentException;
use Moon\Core\Handler\ErrorHandlerInterface;
use Moon\Core\Handler\ExceptionHandler;
use Moon\Core\Handler\ThrowableHandler;
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
     * @throws ContainerExceptionInterface
     * @throws MoonInvalidArgumentException
     * @throws NotFoundExceptionInterface
     * @throws RuntimeException
     */
    public function run(PipelineCollectionInterface $pipelines): void
    {
        $request = $this->container->get('moon.request');
        $response = $this->container->get('moon.response');
        $processor = $this->container->has('moon.webProcessor') ? $this->container->get('moon.webProcessor') : new WebProcessor($this->container);
        $exceptionHandler = $this->container->has('moon.exceptionHandler') ? $this->container->get('moon.exceptionHandler') : new ExceptionHandler();
        $throwableHandler = $this->container->has('moon.throwableHandler') ? $this->container->get('moon.throwableHandler') : new ThrowableHandler();

        if (!$request instanceof ServerRequestInterface) {
            throw new MoonInvalidArgumentException('Request must be a valid ' . ServerRequestInterface::class . ' instance');
        }
        if (!$response instanceof ResponseInterface) {
            throw new MoonInvalidArgumentException('Response must be a valid ' . ResponseInterface::class . ' instance');
        }
        if (!$processor instanceof ProcessorInterface) {
            throw new MoonInvalidArgumentException('Processor must be a valid ' . ProcessorInterface::class . ' instance');
        }
        if (!$exceptionHandler instanceof ErrorHandlerInterface) {
            throw new MoonInvalidArgumentException('ExceptionHandler must be a valid ' . ErrorHandlerInterface::class . ' instance');
        }
        if (!$throwableHandler instanceof ErrorHandlerInterface) {
            throw new MoonInvalidArgumentException('ThrowableHandler must be a valid ' . ErrorHandlerInterface::class . ' instance');
        }

        $matchableRequest = new RequestMatchable($request);

        try {
            // If a pipeline match print the response and return
            if ($response = $this->handlePipelineResponse($pipelines, $matchableRequest, $processor, $response)) {
                $this->sendResponse($response);

                return;
            }

            // If a route pattern matched but the http verbs was different, print a '405 response' and return
            if ($methodNotAllowedResponse = $this->handleMethodNotAllowedResponse($matchableRequest, $response)) {
                $this->sendResponse($methodNotAllowedResponse);

                return;
            }

            // If no route pattern matched, print a '404 response' and return
            if ($routeNotFoundResponse = $this->handleNotFoundResponse($request, $response)) {
                $this->sendResponse($routeNotFoundResponse);

                return;
            }

        } catch (Exception $e) {
            $this->sendResponse($exceptionHandler($e, $request, $response));
        } catch (Throwable $e) {
            $this->sendResponse($throwableHandler($e, $request, $response));
        }
    }

    /**
     * Return a Response for "not found" status
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     *
     * @return null|ResponseInterface
     *
     * @throws ContainerExceptionInterface
     * @throws InvalidArgumentException
     * @throws NotFoundExceptionInterface
     */
    private function handleNotFoundResponse(ServerRequestInterface $request, ResponseInterface $response):?ResponseInterface
    {
        if (!$this->container->has('moon.notFoundHandler')) {

            return $response->withStatus(404);
        }

        $notFoundHandler = $this->container->get('moon.notFoundHandler');

        if ($notFoundHandler instanceof \Closure) {

            return $notFoundHandler($request, $response);
        }

        return null;
    }

    /**
     * Return a Response for "method not allowed" status
     *
     * @param ResponseInterface $response
     * @param RequestMatchable $requestMatchable
     *
     * @return null|ResponseInterface
     *
     * @throws ContainerExceptionInterface
     * @throws InvalidArgumentException
     * @throws NotFoundExceptionInterface
     */
    private function handleMethodNotAllowedResponse(RequestMatchable $requestMatchable, ResponseInterface $response):?ResponseInterface
    {
        if (!$requestMatchable->isPatternMatched()) {

            return null;
        }

        if (!$this->container->has('moon.methodNotAllowedHandler')) {

            return $response->withStatus(405);
        }

        $methodNotAllowedHandler = $this->container->get('moon.methodNotAllowedHandler');

        if ($methodNotAllowedHandler instanceof \Closure) {

            return $methodNotAllowedHandler($requestMatchable->requestWithAddedAttributes(), $response);
        }

        return null;
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
    private function handlePipelineResponse(
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
}