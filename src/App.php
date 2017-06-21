<?php

declare(strict_types=1);

namespace Moon\Core;

use App\Core\Matchable\RequestMatchable;
use Moon\Core\Collection\CliPipelineCollectionInterface;
use Moon\Core\Collection\HttpPipelineCollectionInterface;
use Moon\Core\Pipeline\AbstractPipeline;
use Moon\Core\Pipeline\MatchablePipelineInterface;
use Moon\Core\Pipeline\PipelineInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class App extends AbstractPipeline implements PipelineInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * TODO Refactor this huge method
     * @param HttpPipelineCollectionInterface $pipelines
     *
     * @return ResponseInterface
     * @throws \InvalidArgumentException
     */
    public function runWeb(HttpPipelineCollectionInterface $pipelines): ResponseInterface
    {
        try {
            $request = $this->container->get('request');
        } catch (NotFoundExceptionInterface | ContainerExceptionInterface $e) {
            throw new \InvalidArgumentException('Request is required in the container for web application'); // TODO use custom exception
        }
        if (!$request instanceof RequestInterface) {
            throw new \InvalidArgumentException('Request must be a valid ' . RequestInterface::class . ' instance'); // TODO use custom exception
        }

        try {
            $response = $this->container->get('response');
        } catch (NotFoundExceptionInterface | ContainerExceptionInterface $e) {
            throw new \InvalidArgumentException('Response is required in the container for web application'); // TODO use custom exception
        }
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

    public function runCli(CliPipelineCollectionInterface $pipelines): void
    {

    }

    private function processStages(array $stages)
    {

    }
}