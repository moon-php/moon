<?php

declare(strict_types=1);

namespace Moon\Core\Collection;

use Moon\Core\Pipeline\HttpPipeline;

class HttpPipelineArrayCollection implements HttpPipelineCollectionInterface
{
    protected $pipelines;

    public function __construct(array $pipelines = [])
    {
        foreach ($pipelines as $key => $pipeline) {
            if (!$pipeline instanceof HttpPipeline) {
                throw new \InvalidArgumentException('All pipelines must implement ' . HttpPipeline::class);
            }
        }

        $this->pipelines = $pipelines;
    }

    public function add(HttpPipeline $pipeline): void
    {
        $this->pipelines[] = $pipeline;
    }

    public function addArray(array $pipelines): void
    {
        foreach ($pipelines as $key => $pipeline) {
            if (!$pipeline instanceof HttpPipeline) {
                throw new \InvalidArgumentException('All pipelines must implement ' . HttpPipeline::class);
            }
        }

        $this->pipelines = array_merge($this->pipelines, $pipelines);
    }

    public function merge(HttpPipelineCollectionInterface $pipelineCollection): void
    {
        $this->pipelines = array_merge($this->pipelines, $pipelineCollection->toArray());
    }

    public function toArray(): array
    {
        return $this->pipelines;
    }

    public function getIterator(): \Generator
    {
        foreach ($this->pipelines as $key => $pipeline) {
            yield $key => $pipeline;
        }
    }
}