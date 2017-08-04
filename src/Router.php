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
     * @var MatchablePipelineArrayCollection $pipelineCollection
     */
    private $pipelineCollection;

    /**
     * @var string $prefix
     */
    private $prefix;

    /**
     * @var callable|string|HttpPipeline|array $routerStages
     */
    private $routerStages;

    /**
     * Router constructor.
     *
     * @param string $prefix
     * @param callable|string|HttpPipeline|array $routerStages
     */
    public function __construct(string $prefix = '', $routerStages = null)
    {
        $this->prefix = $prefix;
        $this->pipelineCollection = new MatchablePipelineArrayCollection();
        $this->routerStages = $routerStages;
    }

    /**
     * Add a 'GET' route to be handled by the application
     *
     * @param string $pattern
     * @param callable|string|HttpPipeline|array $stages $stages
     *
     * @return void
     */
    public function get(string $pattern, $stages): void
    {
        $pipeline = new HttpPipeline(RequestMethodInterface::METHOD_GET, $this->prefix . $pattern, $this->routerStages);
        $pipeline->pipe($stages);
        $this->pipelineCollection->add($pipeline);

    }

    /**
     * Add a 'POST' route to be handled by the application
     *
     * @param string $pattern
     * @param callable|string|HttpPipeline|array $stages $stages
     *
     * @return void
     */
    public function post(string $pattern, $stages): void
    {
        $pipeline = new HttpPipeline(RequestMethodInterface::METHOD_POST, $this->prefix . $pattern, $this->routerStages);
        $pipeline->pipe($stages);
        $this->pipelineCollection->add($pipeline);
    }

    /**
     * Add a 'PUT' route to be handled by the application
     *
     * @param string $pattern
     * @param callable|string|HttpPipeline|array $stages $stages
     *
     * @return void
     */
    public function put(string $pattern, $stages): void
    {
        $pipeline = new HttpPipeline(RequestMethodInterface::METHOD_PUT, $this->prefix . $pattern, $this->routerStages);
        $pipeline->pipe($stages);
        $this->pipelineCollection->add($pipeline);
    }

    /**
     * Add a 'PATCH' route to be handled by the application
     *
     * @param string $pattern
     * @param callable|string|HttpPipeline|array $stages $stages
     *
     * @return void
     */
    public function patch(string $pattern, $stages): void
    {
        $pipeline = new HttpPipeline(RequestMethodInterface::METHOD_PATCH, $this->prefix . $pattern, $this->routerStages);
        $pipeline->pipe($stages);
        $this->pipelineCollection->add($pipeline);
    }

    /**
     * Add a 'DELETE' route to be handled by the application
     *
     * @param string $pattern
     * @param callable|string|HttpPipeline|array $stages $stages
     *
     * @return void
     */
    public function delete(string $pattern, $stages): void
    {
        $pipeline = new HttpPipeline(RequestMethodInterface::METHOD_DELETE, $this->prefix . $pattern, $this->routerStages);
        $pipeline->pipe($stages);
        $this->pipelineCollection->add($pipeline);
    }

    /**
     * Add a 'OPTIONS' route to be handled by the application
     *
     * @param string $pattern
     * @param callable|string|HttpPipeline|array $stages $stages
     *
     * @return void
     */
    public function options(string $pattern, $stages): void
    {
        $pipeline = new HttpPipeline(RequestMethodInterface::METHOD_OPTIONS, $this->prefix . $pattern, $this->routerStages);
        $pipeline->pipe($stages);
        $this->pipelineCollection->add($pipeline);
    }

    /**
     * Add a 'HEAD' route to be handled by the application
     *
     * @param string $pattern
     * @param callable|string|HttpPipeline|array $stages $stages
     *
     * @return void
     */
    public function head(string $pattern, $stages): void
    {
        $pipeline = new HttpPipeline(RequestMethodInterface::METHOD_HEAD, $this->prefix . $pattern, $this->routerStages);
        $pipeline->pipe($stages);
        $this->pipelineCollection->add($pipeline);
    }

    /**
     * Add a 'PURGE' route to be handled by the application
     *
     * @param string $pattern
     * @param callable|string|HttpPipeline|array $stages $stages
     *
     * @return void
     */
    public function purge(string $pattern, $stages): void
    {
        $pipeline = new HttpPipeline(RequestMethodInterface::METHOD_PURGE, $this->prefix . $pattern, $this->routerStages);
        $pipeline->pipe($stages);
        $this->pipelineCollection->add($pipeline);
    }

    /**
     * Add a 'TRACE' route to be handled by the application
     *
     * @param string $pattern
     * @param callable|string|HttpPipeline|array $stages $stages
     *
     * @return void
     */
    public function trace(string $pattern, $stages): void
    {
        $pipeline = new HttpPipeline(RequestMethodInterface::METHOD_TRACE, $this->prefix . $pattern, $this->routerStages);
        $pipeline->pipe($stages);
        $this->pipelineCollection->add($pipeline);
    }

    /**
     * Add a 'CONNECT' route to be handled by the application
     *
     * @param string $pattern
     * @param callable|string|HttpPipeline|array $stages $stages
     *
     * @return void
     */
    public function connect(string $pattern, $stages): void
    {
        $pipeline = new HttpPipeline(RequestMethodInterface::METHOD_CONNECT, $this->prefix . $pattern, $this->routerStages);
        $pipeline->pipe($stages);
        $this->pipelineCollection->add($pipeline);
    }

    /**
     * Add multiple verb to a pattern and stages
     *
     * @param string $pattern
     * @param array $verbs
     * @param callable|string|HttpPipeline|array $stages $stages
     *
     * @return void
     *
     * @throws \Moon\Moon\Exception\InvalidArgumentException
     */
    public function map(string $pattern, array $verbs, $stages): void
    {
        $pipeline = new HttpPipeline($verbs, $this->prefix . $pattern, $this->routerStages);
        $pipeline->pipe($stages);
        $this->pipelineCollection->add($pipeline);
    }

    /**
     * Return all pipelines generated by the router
     *
     * @return MatchablePipelineCollectionInterface
     */
    public function pipelines(): MatchablePipelineCollectionInterface
    {
        return $this->pipelineCollection;
    }
}