<?php

declare(strict_types=1);

namespace Moon\Moon;

use Fig\Http\Message\RequestMethodInterface;
use Moon\Moon\Collection\MatchablePipelineArrayCollection;
use Moon\Moon\Collection\MatchablePipelineCollectionInterface;
use Moon\Moon\Pipeline\HttpPipeline;

class Router
{
    /**
     * @var MatchablePipelineArrayCollection
     */
    private $pipelineCollection;

    /**
     * @var string
     */
    private $prefix;

    /**
     * @var callable|string|HttpPipeline|array|null
     */
    private $routerStages;

    /**
     * Router constructor.
     *
     * @param callable|string|HttpPipeline|array $routerStages
     */
    public function __construct(string $prefix = '', $routerStages = null)
    {
        $this->prefix = $prefix;
        $this->pipelineCollection = new MatchablePipelineArrayCollection();
        $this->routerStages = $routerStages;
    }

    /**
     * Add a 'GET' route to be handled by the application.
     *
     * @param callable|string|HttpPipeline|array $stages $stages
     */
    public function get(string $pattern, $stages): void
    {
        $pipeline = new HttpPipeline(RequestMethodInterface::METHOD_GET, $this->prefix.$pattern, $this->routerStages);
        $pipeline->pipe($stages);
        $this->pipelineCollection->add($pipeline);
    }

    /**
     * Add a 'POST' route to be handled by the application.
     *
     * @param callable|string|HttpPipeline|array $stages $stages
     */
    public function post(string $pattern, $stages): void
    {
        $pipeline = new HttpPipeline(RequestMethodInterface::METHOD_POST, $this->prefix.$pattern, $this->routerStages);
        $pipeline->pipe($stages);
        $this->pipelineCollection->add($pipeline);
    }

    /**
     * Add a 'PUT' route to be handled by the application.
     *
     * @param callable|string|HttpPipeline|array $stages $stages
     */
    public function put(string $pattern, $stages): void
    {
        $pipeline = new HttpPipeline(RequestMethodInterface::METHOD_PUT, $this->prefix.$pattern, $this->routerStages);
        $pipeline->pipe($stages);
        $this->pipelineCollection->add($pipeline);
    }

    /**
     * Add a 'PATCH' route to be handled by the application.
     *
     * @param callable|string|HttpPipeline|array $stages $stages
     */
    public function patch(string $pattern, $stages): void
    {
        $pipeline = new HttpPipeline(RequestMethodInterface::METHOD_PATCH, $this->prefix.$pattern, $this->routerStages);
        $pipeline->pipe($stages);
        $this->pipelineCollection->add($pipeline);
    }

    /**
     * Add a 'DELETE' route to be handled by the application.
     *
     * @param callable|string|HttpPipeline|array $stages $stages
     */
    public function delete(string $pattern, $stages): void
    {
        $pipeline = new HttpPipeline(RequestMethodInterface::METHOD_DELETE, $this->prefix.$pattern, $this->routerStages);
        $pipeline->pipe($stages);
        $this->pipelineCollection->add($pipeline);
    }

    /**
     * Add a 'OPTIONS' route to be handled by the application.
     *
     * @param callable|string|HttpPipeline|array $stages $stages
     */
    public function options(string $pattern, $stages): void
    {
        $pipeline = new HttpPipeline(RequestMethodInterface::METHOD_OPTIONS, $this->prefix.$pattern, $this->routerStages);
        $pipeline->pipe($stages);
        $this->pipelineCollection->add($pipeline);
    }

    /**
     * Add a 'HEAD' route to be handled by the application.
     *
     * @param callable|string|HttpPipeline|array $stages $stages
     */
    public function head(string $pattern, $stages): void
    {
        $pipeline = new HttpPipeline(RequestMethodInterface::METHOD_HEAD, $this->prefix.$pattern, $this->routerStages);
        $pipeline->pipe($stages);
        $this->pipelineCollection->add($pipeline);
    }

    /**
     * Add a 'PURGE' route to be handled by the application.
     *
     * @param callable|string|HttpPipeline|array $stages $stages
     */
    public function purge(string $pattern, $stages): void
    {
        $pipeline = new HttpPipeline(RequestMethodInterface::METHOD_PURGE, $this->prefix.$pattern, $this->routerStages);
        $pipeline->pipe($stages);
        $this->pipelineCollection->add($pipeline);
    }

    /**
     * Add a 'TRACE' route to be handled by the application.
     *
     * @param callable|string|HttpPipeline|array $stages $stages
     */
    public function trace(string $pattern, $stages): void
    {
        $pipeline = new HttpPipeline(RequestMethodInterface::METHOD_TRACE, $this->prefix.$pattern, $this->routerStages);
        $pipeline->pipe($stages);
        $this->pipelineCollection->add($pipeline);
    }

    /**
     * Add a 'CONNECT' route to be handled by the application.
     *
     * @param callable|string|HttpPipeline|array $stages $stages
     */
    public function connect(string $pattern, $stages): void
    {
        $pipeline = new HttpPipeline(RequestMethodInterface::METHOD_CONNECT, $this->prefix.$pattern, $this->routerStages);
        $pipeline->pipe($stages);
        $this->pipelineCollection->add($pipeline);
    }

    /**
     * Add multiple verb to a pattern and stages.
     *
     * @param array                              $verbs
     * @param callable|string|HttpPipeline|array $stages $stages
     *
     * @throws \Moon\Moon\Exception\InvalidArgumentException
     */
    public function map(string $pattern, array $verbs, $stages): void
    {
        $pipeline = new HttpPipeline($verbs, $this->prefix.$pattern, $this->routerStages);
        $pipeline->pipe($stages);
        $this->pipelineCollection->add($pipeline);
    }

    /**
     * Return all pipelines generated by the router.
     */
    public function pipelines(): MatchablePipelineCollectionInterface
    {
        return $this->pipelineCollection;
    }
}
