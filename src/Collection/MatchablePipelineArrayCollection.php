<?php

declare(strict_types=1);

namespace Moon\Moon\Collection;

use Moon\Moon\Exception\InvalidArgumentException;
use Moon\Moon\Pipeline\MatchablePipelineInterface;
use function array_merge;
use function gettype;
use function sprintf;

class MatchablePipelineArrayCollection implements MatchablePipelineCollectionInterface
{
    /**
     * @var array $pipelines
     */
    private $pipelines = [];

    /**
     * MatchablePipelineArrayCollection constructor.
     *
     * @param array $pipelines
     *
     * @throws InvalidArgumentException
     */
    public function __construct(array $pipelines = [])
    {
        $this->addArray($pipelines);
    }

    /**
     * {@inheritdoc}
     */
    public function add(MatchablePipelineInterface $pipeline): void
    {
        $this->pipelines[] = $pipeline;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Moon\Moon\Exception\InvalidArgumentException
     */
    public function addArray(array $pipelines): void
    {
        foreach ($pipelines as $key => $pipeline) {
            if (!$pipeline instanceof MatchablePipelineInterface) {
                throw new InvalidArgumentException(
                    sprintf('All pipelines must implement %s, %s given', MatchablePipelineInterface::class, gettype($pipeline))
                );
            }
        }

        $this->pipelines = array_merge($this->pipelines, $pipelines);
    }

    /**
     * {@inheritdoc}
     */
    public function merge(MatchablePipelineCollectionInterface $pipelineCollection): void
    {
        $this->pipelines = array_merge($this->pipelines, $pipelineCollection->toArray());
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return $this->pipelines;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): \Generator
    {
        foreach ($this->pipelines as $key => $pipeline) {
            yield $key => $pipeline;
        }
    }
}