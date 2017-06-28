<?php

declare(strict_types=1);

namespace Moon\Core;

use Moon\Core\Collection\CliPipelineCollectionInterface;
use Moon\Core\Collection\HttpPipelineCollectionInterface;
use Moon\Core\Exception\Exception;
use Moon\Core\Exception\InvalidArgumentException;
use Moon\Core\Matchable\RequestMatchable;
use Moon\Core\Matchable\StringMatchable;
use Moon\Core\Pipeline\AbstractPipeline;
use Moon\Core\Pipeline\HttpPipeline;
use Moon\Core\Pipeline\MatchablePipelineInterface;
use Moon\Core\Pipeline\PipelineInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

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
     * @throws InvalidArgumentException
     */
    public function runWeb(HttpPipelineCollectionInterface $pipelines): ResponseInterface
    {
        $request = $this->container->get('request');
        $response = $this->container->get('response');

        if (!$request instanceof RequestInterface) {
            throw new InvalidArgumentException('Request must be a valid ' . RequestInterface::class . ' instance');
        }
        if (!$response instanceof ResponseInterface) {
            throw new InvalidArgumentException('Response must be a valid ' . ResponseInterface::class . ' instance');
        }

        $matchableRequest = new RequestMatchable($request);
        /** @var HttpPipeline $pipeline */
        foreach ($pipelines as $pipeline) {
            if ($pipeline->matchBy($matchableRequest)) {
                $pipelineResponse = $this->processStages(array_merge($this->stages(), $pipeline->stages()));

                if ($pipelineResponse instanceof ResponseInterface) {
                    return $pipelineResponse;
                }

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
     * Handle all the passed stages
     *
     * @param array $stages
     *
     * @return ResponseInterface|string
     */
    protected function processStages(array $stages)
    {
        // TODO Implement logic here
    }

    /**
     * Return a Response for "not found" status
     *
     * @param ResponseInterface $response
     *
     * @return null|ResponseInterface
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
     */
    public function runCli(CliPipelineCollectionInterface $pipelines): void
    {
        $argument = $this->container->get('cliArguments');

        $matchableString = new StringMatchable($argument);

        /** @var MatchablePipelineInterface $pipeline */
        foreach ($pipelines as $pipeline) {
            if ($pipeline->matchBy($matchableString)) {
                $this->processCliStages(array_merge($this->stages(), $pipeline->stages()), $argument);
            }
        }
    }

    protected function processCliStages(array $stages, $payload): void
    {
        // TODO implement logic here
    }
}