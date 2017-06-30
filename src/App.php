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
     * @return ResponseInterface
     *
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws InvalidArgumentException
     */
    public function runWeb(HttpPipelineCollectionInterface $pipelines): ResponseInterface
    {
        $request = $this->container->get('request');
        $response = $this->container->get('response');
        $processor = $this->container->has('webProcessor') ? $this->container->get('webProcessor') : $this->createWebProcessor();

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
                $pipelineResponse = $processor->processStages(array_merge($this->stages(), $pipeline->stages()));

                if ($pipelineResponse instanceof ResponseInterface) {

                    return $pipelineResponse;
                }

                // TODO create stream and handle the response
                return $response->withBody($pipelineResponse);
            }
        }

        if ($methodNotAllowedResponse = $this->handleMethodNotAllowedResponse($response, $matchableRequest)) {

            return $methodNotAllowedResponse;
        }

        if ($routeNotFoundResponse = $this->handleNotFoundResponse($response)) {

            return $routeNotFoundResponse;
        }

        /** @var ResponseInterface $response */
        return $response->withStatus(500);
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
        if (!$this->container->has('notFoundHandler')) {

            return $response->withStatus(404);
        }

        $notFoundHandler = $this->container->get('notFoundHandler');

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

        if (!$this->container->has('methodNotAllowedHandler')) {

            return $response->withStatus(405);
        }

        $notFoundHandler = $this->container->get('methodNotAllowedHandler');

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
        $input = $this->container->get('input');
        $processor = $this->container->has('cliProcessor') ? $this->container->get('cliProcessor') : $this->createCliProcessor();

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