<?php

declare(strict_types=1);

namespace Moon\Moon\Collection;

use Moon\Moon\Exception\InvalidArgumentException;
use Moon\Moon\Pipeline\MatchablePipelineInterface;

class MatchablePipelineArrayCollection implements MatchablePipelineCollectionInterface
{
    /**
     * @var array
     */
    private $pipelines = [];

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(array $pipelines = [])
    {
        $this->addArray($pipelines);
    }

    public function add(MatchablePipelineInterface $pipeline): void
    {
        $this->pipelines[] = $pipeline;
    }

    /**
     * @throws \Moon\Moon\Exception\InvalidArgumentException
     */
    public function addArray(array $pipelines): void
    {
        foreach ($pipelines as $key => $pipeline) {
            if (!$pipeline instanceof MatchablePipelineInterface) {
                throw new InvalidArgumentException(
                    \sprintf('All pipelines must implement %s, %s given', MatchablePipelineInterface::class, \gettype($pipeline))
                );
            }
        }

        $this->pipelines = \array_merge($this->pipelines, $pipelines);
    }

    public function merge(MatchablePipelineCollectionInterface $pipelineCollection): void
    {
        $this->pipelines = \array_merge($this->pipelines, $pipelineCollection->toArray());
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
