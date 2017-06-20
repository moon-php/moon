<?php

declare(strict_types=1);

namespace Moon\Core;

use App\Core\Matchable\RequestMatchable;
use Moon\Core\Pipeline\MatchablePipelineInterface;
use Moon\Core\Pipeline\PipelineInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class App implements PipelineInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var array
     */
    private $stages = [];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function pipe($stages): void
    {
        if (!is_array($stages)) {
            $this->stages[] = $stages;
        }

        foreach ($stages as $stage) {
            $this->pipe($stage);
        }
    }

    public function stages(): array
    {
        return $this->stages;
    }

    /**
     * TODO Refactor thi huge method
     * @param MatchablePipelineInterface[] $pipelines
     *
     * @return ResponseInterface
     * @throws \InvalidArgumentException
     */
    public function runWeb(array $pipelines): ResponseInterface
    {
        if ($this->container->has('request') === false) {
            throw new \InvalidArgumentException('Request is required in the container for web application'); // TODO use custom exception
        }
        $request = $this->container->get('request');
        if (!$request instanceof RequestInterface) {
            throw new \InvalidArgumentException('Request must be a valid ' . RequestInterface::class . ' instance'); // TODO use custom exception
        }

        if ($this->container->has('response') === false) {
            throw new \InvalidArgumentException('Response is required in the container for web application'); // TODO use custom exception
        }
        $response = $this->container->get('response');
        if (!$response instanceof ResponseInterface) {
            throw new \InvalidArgumentException('Response must be a valid ' . ResponseInterface::class . ' instance'); // TODO use custom exception
        }

        $matchableRequest = new RequestMatchable($request);

        /** @var MatchablePipelineInterface $pipeline TODO Add collections */
        foreach ($pipelines as $pipeline) {
            if ($pipeline->matchBy($matchableRequest)) {
                $pipelineResponse = $this->processStages(array_merge($this->stages(), $pipeline->stages()));

                if ($pipelineResponse instanceof ResponseInterface) {
                    return $pipelineResponse;
                }

                return $response->withBody($pipelineResponse);
            }
        }

        if (!$this->container->has('notFoundHandler')) {
            return $response->withStatus(404);
        }

        $notFoundHandler = $this->container->get('notFoundHandler');

        if ($notFoundHandler instanceof \Closure) {
            return $notFoundHandler();
        }
        /** @var ResponseInterface $response */
        return $response->withStatus(500);
    }

    public function runCli(array $pipelines): void
    {

    }

    private function processStages(array $stages)
    {

    }
}