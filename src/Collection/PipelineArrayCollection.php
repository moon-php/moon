<?php

declare(strict_types=1);

namespace Moon\Moon\Collection;

use Moon\Moon\Exception\InvalidArgumentException;
use Moon\Moon\Pipeline\PipelineInterface;
use function array_merge;
use function gettype;
use function sprintf;

class PipelineArrayCollection implements PipelineCollectionInterface
{
    /**
     * @var array $pipelines
     */
    private $pipelines = [];

    /**
     * PipelineArrayCollection constructor.
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
    public function add(PipelineInterface $pipeline): void
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
            if (!$pipeline instanceof PipelineInterface) {
                throw new InvalidArgumentException(
                    sprintf('All pipelines must implement %s, %s given', PipelineInterface::class, gettype($pipeline))
                );
            }
        }

        $this->pipelines = array_merge($this->pipelines, $pipelines);
    }

    /**
     * {@inheritdoc}
     */
    public function merge(PipelineCollectionInterface $pipelineCollection): void
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