<?php

declare(strict_types=1);

namespace Moon\Core;

use Moon\Core\Collection\CliPipelineCollectionInterface;
use Moon\Core\Collection\HttpPipelineCollectionInterface;
use Moon\Core\Exception\InvalidArgumentException;
use Moon\Core\Matchable\RequestMatchable;
use Moon\Core\Matchable\StringMatchable;
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
    protected $container;

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
     * @param HttpPipelineCollectionInterface $pipelines
     *
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws InvalidArgumentException
     */
    public function runWeb(HttpPipelineCollectionInterface $pipelines): void
    {
        $request = $this->container->get('moon.request');
        $response = $this->container->get('moon.response');
        $stream = $this->container->get('moon.stream');
        $processor = $this->container->has('moon.webProcessor') ? $this->container->get('moon.webProcessor') : $this->createWebProcessor();

        if (!$request instanceof ServerRequestInterface) {
            throw new InvalidArgumentException('Request must be a valid ' . ServerRequestInterface::class . ' instance');
        }
        if (!$response instanceof ResponseInterface) {
            throw new InvalidArgumentException('Response must be a valid ' . ResponseInterface::class . ' instance');
        }
        if (!$stream instanceof StreamInterface) {
            throw new InvalidArgumentException('Stream must be a valid ' . StreamInterface::class . ' instance');
        }
        if (!$processor instanceof ProcessorInterface) {
            throw new InvalidArgumentException('Processor must be a valid ' . ProcessorInterface::class . ' instance');
        }

        $matchableRequest = new RequestMatchable($request);

        /** @var HttpPipeline $pipeline */
        foreach ($pipelines as $pipeline) {
            if ($pipeline->matchBy($matchableRequest)) {
                $pipelineResponse = $processor->processStages(array_merge($this->stages(), $pipeline->stages()));

                if ($pipelineResponse instanceof ResponseInterface) {
                    $this->sendResponse($pipelineResponse);

                    return;
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
            $chunk = $this->container->has('moon.chunkSize');
            while (!$body->eof()) {
                echo $body->read($chunk);
            }

            return;
        }

        echo $body->getContents();
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

    /**
     * Create a default WebProcessor
     *
     * @return WebProcessor
     */
    private function createWebProcessor(): WebProcessor
    {
        return new WebProcessor($this->container);
    }

    ####################################################################################################################
    ####################################################################################################################
    # CLI
    ####################################################################################################################
    ####################################################################################################################

    /**
     * Run the cli application
     *
     * @param CliPipelineCollectionInterface $pipelines
     *
     * @return void
     *
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Moon\Core\Exception\InvalidArgumentException
     */
    public function runCli(CliPipelineCollectionInterface $pipelines): void
    {
        $input = $this->container->get('moon.input');
        $processor = $this->container->has('moon.cliProcessor') ? $this->container->get('moon.cliProcessor') : $this->createCliProcessor();

        if (!$input instanceof InputInterface) {
            throw new InvalidArgumentException('input must be a valid ' . InputInterface::class . ' instance');
        }
        if (!$processor instanceof ProcessorInterface) {
            throw new InvalidArgumentException('Processor must be a valid ' . ProcessorInterface::class . ' instance');
        }
        $matchableString = new StringMatchable($input->toString());

        /** @var MatchablePipelineInterface $pipeline */
        foreach ($pipelines as $pipeline) {
            if ($pipeline->matchBy($matchableString)) {
                $processor->processStages(array_merge($this->stages(), $pipeline->stages()));
            }
        }
    }

    /**
     * Create a default WebProcessor
     *
     * @return CliProcessor
     */
    private function createCliProcessor(): CliProcessor
    {
        return new CliProcessor($this->container);
    }
}