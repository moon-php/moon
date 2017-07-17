<?php

declare(strict_types=1);

namespace Moon\Core;

use Moon\Core\Collection\PipelineCollectionInterface;
use Moon\Core\Exception\InvalidArgumentException;
use Moon\Core\Input\Input;
use Moon\Core\Input\InputInterface;
use Moon\Core\Matchable\InputMatchable;
use Moon\Core\Matchable\RequestMatchable;
use Moon\Core\Pipeline\AbstractPipeline;
use Moon\Core\Pipeline\HttpPipeline;
use Moon\Core\Pipeline\MatchablePipelineInterface;
use Moon\Core\Pipeline\PipelineInterface;
use Moon\Core\Processor\CliProcessor;
use Moon\Core\Processor\ProcessorInterface;
use Moon\Core\Processor\WebProcessor;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;

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

    ####################################################################################################################
    ####################################################################################################################
    # WEB
    ####################################################################################################################
    ####################################################################################################################

    /**
     * Run the web application, and return a Response
     *
     * @param PipelineCollectionInterface $pipelines
     *
     * @return void
     *
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws InvalidArgumentException
     */
    public function runWeb(PipelineCollectionInterface $pipelines): void
    {
        $request = $this->container->get('moon.request');
        $response = $this->container->get('moon.response');
        $processor = $this->container->has('moon.webProcessor') ? $this->container->get('moon.webProcessor') : new WebProcessor($this->container);

        if (!$request instanceof ServerRequestInterface) {
            throw new InvalidArgumentException('Request must be a valid ' . ServerRequestInterface::class . ' instance');
        }
        if (!$response instanceof ResponseInterface) {
            throw new InvalidArgumentException('Response must be a valid ' . ResponseInterface::class . ' instance');
        }
        if (!$processor instanceof ProcessorInterface) {
            throw new InvalidArgumentException('Processor must be a valid ' . ProcessorInterface::class . ' instance');
        }

        $matchableRequest = new RequestMatchable($request);

        /** @var HttpPipeline $pipeline */
        foreach ($pipelines as $pipeline) {
            if ($pipeline->matchBy($matchableRequest)) {

                $pipelineResponse = $processor->processStages(
                    array_merge($this->stages(), $pipeline->stages()),
                    $matchableRequest->requestWithAddedAttributes()
                );

                if ($pipelineResponse instanceof ResponseInterface) {
                    $this->sendResponse($pipelineResponse);

                    return;
                }

                $stream = $this->container->get('moon.stream');
                if (!$stream instanceof StreamInterface) {
                    throw new InvalidArgumentException('Stream must be a valid ' . StreamInterface::class . ' instance');
                }

                $stream->write($pipelineResponse);
                $this->sendResponse($response->withBody($stream));

                return;
            }
        }

        if ($methodNotAllowedResponse = $this->handleMethodNotAllowedResponse($response, $matchableRequest)) {
            $this->sendResponse($methodNotAllowedResponse);

            return;
        }

        if ($routeNotFoundResponse = $this->handleNotFoundResponse($response)) {
            $this->sendResponse($routeNotFoundResponse);

            return;
        }

        /** @var ResponseInterface $response */
        $this->sendResponse($response->withStatus(500));
    }

    /**
     * Send headers and body to the client
     *
     * @param ResponseInterface $response
     *
     * @return void
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
     * Return a Response for "not found" status
     *
     * @param ResponseInterface $response
     *
     * @return null|ResponseInterface
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    private function handleNotFoundResponse(ResponseInterface $response):?ResponseInterface
    {
        if (!$this->container->has('moon.notFoundHandler')) {

            return $response->withStatus(404);
        }

        $notFoundHandler = $this->container->get('moon.notFoundHandler');

        if ($notFoundHandler instanceof \Closure) {

            return $notFoundHandler();
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
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    private function handleMethodNotAllowedResponse(ResponseInterface $response, RequestMatchable $requestMatchable):?ResponseInterface
    {
        if (!$requestMatchable->isPatternMatched()) {

            return null;
        }

        if (!$this->container->has('moon.methodNotAllowedHandler')) {

            return $response->withStatus(405);
        }

        $notFoundHandler = $this->container->get('moon.methodNotAllowedHandler');

        if ($notFoundHandler instanceof \Closure) {

            return $notFoundHandler();
        }

        return null;
    }

    ####################################################################################################################
    ####################################################################################################################
    # CLI
    ####################################################################################################################
    ####################################################################################################################

    /**
     * Run the cli application
     *
     * @param PipelineCollectionInterface $pipelines
     *
     * @return void
     *
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Moon\Core\Exception\InvalidArgumentException
     */
    public function runCli(PipelineCollectionInterface $pipelines): void
    {
        $input = $this->container->has('moon.cliInput') ? $this->container->get('moon.cliInput') : new Input($GLOBALS['argv']);
        $processor = $this->container->has('moon.cliProcessor') ? $this->container->get('moon.cliProcessor') : new CliProcessor($this->container);

        if (!$input instanceof InputInterface) {
            throw new InvalidArgumentException('Input must be a valid ' . InputInterface::class . ' instance');
        }
        if (!$processor instanceof ProcessorInterface) {
            throw new InvalidArgumentException('Processor must be a valid ' . ProcessorInterface::class . ' instance');
        }
        $matchableString = new InputMatchable($input);

        /** @var MatchablePipelineInterface $pipeline */
        foreach ($pipelines as $pipeline) {
            if ($pipeline->matchBy($matchableString)) {
                $processor->processStages(array_merge($this->stages(), $pipeline->stages()), $input);
            }
        }
    }
}