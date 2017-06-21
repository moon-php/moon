<?php

declare(strict_types=1);

namespace Moon\Core\Collection;

use Moon\Core\Pipeline\CliPipeline;

class CliPipelineArrayCollection implements CliPipelineCollectionInterface
{
    protected $pipelines;

    public function __construct(array $pipelines = [])
    {
        foreach ($pipelines as $key => $pipeline) {
            if (!$pipeline instanceof CliPipeline) {
                throw new \InvalidArgumentException('All pipelines must implement ' . CliPipeline::class);
            }
        }

        $this->pipelines = $pipelines;
    }

    public function add(CliPipeline $pipeline): void
    {
        $this->pipelines[] = $pipeline;
    }

    public function addArray(array $pipelines): void
    {
        foreach ($pipelines as $key => $pipeline) {
            if (!$pipeline instanceof CliPipeline) {
                throw new \InvalidArgumentException('All pipelines must implement ' . CliPipeline::class);
            }
        }

        $this->pipelines = array_merge($this->pipelines, $pipelines);
    }

    public function merge(CliPipelineCollectionInterface $pipelineCollection): void
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