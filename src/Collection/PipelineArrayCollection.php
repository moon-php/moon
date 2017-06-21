<?php

declare(strict_types=1);

namespace Moon\Core\Collection;

use Moon\Core\Pipeline\PipelineInterface;

class PipelineArrayCollection implements PipelineCollectionInterface
{
    protected $pipelines;

    public function __construct(array $pipelines= [])
    {
        foreach ($pipelines as $key => $pipeline) {
            if (!$pipeline instanceof PipelineInterface) {
                throw new \InvalidArgumentException('All pipelines must implement ' . PipelineInterface::class);
            }
        }

        $this->pipelines = $pipelines;
    }

    public function add(PipelineInterface $pipeline): void
    {
        $this->pipelines[] = $pipeline;
    }

    public function addArray(array $pipelines): void
    {
        foreach ($pipelines as $key => $pipeline) {
            if (!$pipeline instanceof PipelineInterface) {
                throw new \InvalidArgumentException('All pipelines must implement ' . PipelineInterface::class);
            }
        }

        $this->pipelines = array_merge($this->pipelines, $pipelines);
    }

    public function merge(PipelineCollectionInterface $pipelineCollection): void
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