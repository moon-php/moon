<?php

declare(strict_types=1);

namespace Moon\Core;

use Moon\Core\Collection\HttpPipelineArrayCollection;
use Moon\Core\Collection\HttpPipelineCollectionInterface;
use Moon\Core\Pipeline\HttpPipeline;

class Router
{
    protected $httpPipelines = [];

    public function get(string $pattern, array $stages): void
    {
        $pipeline = new HttpPipeline('GET', $pattern);
        $pipeline->pipe($stages);
        $this->httpPipelines[] = $pipeline;
    }

    public function post(string $pattern, array $stages): void
    {
        $pipeline = new HttpPipeline('POST', $pattern);
        $pipeline->pipe($stages);
        $this->httpPipelines[] = $pipeline;
    }

    public function put(string $pattern, array $stages): void
    {
        $pipeline = new HttpPipeline('PUT', $pattern);
        $pipeline->pipe($stages);
        $this->httpPipelines[] = $pipeline;
    }

    public function patch(string $pattern, array $stages): void
    {
        $pipeline = new HttpPipeline('PATCH', $pattern);
        $pipeline->pipe($stages);
        $this->httpPipelines[] = $pipeline;
    }

    public function delete(string $pattern, array $stages): void
    {
        $pipeline = new HttpPipeline('DELETE', $pattern);
        $pipeline->pipe($stages);
        $this->httpPipelines[] = $pipeline;
    }

    public function option(string $pattern, array $stages): void
    {
        $pipeline = new HttpPipeline('OPTION', $pattern);
        $pipeline->pipe($stages);
        $this->httpPipelines[] = $pipeline;
    }

    public function pipelines(): HttpPipelineCollectionInterface
    {
        return new HttpPipelineArrayCollection($this->httpPipelines);
    }
}