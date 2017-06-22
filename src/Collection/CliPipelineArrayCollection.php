<?php

declare(strict_types=1);

namespace Moon\Core\Collection;

use Moon\Core\Exception\InvalidArgumentException;
use Moon\Core\Pipeline\CliPipeline;

class CliPipelineArrayCollection implements CliPipelineCollectionInterface
{
    /**
     * @var array $pipelines
     */
    protected $pipelines;

    /**
     * CliPipelineArrayCollection constructor.
     *
     * @param array $pipelines
     *
     * @throws \Moon\Core\Exception\InvalidArgumentException
     */
    public function __construct(array $pipelines = [])
    {
        foreach ($pipelines as $key => $pipeline) {
            if (!$pipeline instanceof CliPipeline) {
                throw new InvalidArgumentException('All pipelines must implement ' . CliPipeline::class);
            }
        }

        $this->pipelines = $pipelines;
    }

    /**
     * {@inheritdoc}
     */
    public function add(CliPipeline $pipeline): void
    {
        $this->pipelines[] = $pipeline;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Moon\Core\Exception\InvalidArgumentException
     */
    public function addArray(array $pipelines): void
    {
        foreach ($pipelines as $key => $pipeline) {
            if (!$pipeline instanceof CliPipeline) {
                throw new InvalidArgumentException('All pipelines must implement ' . CliPipeline::class);
            }
        }

        $this->pipelines = array_merge($this->pipelines, $pipelines);
    }

    /**
     * {@inheritdoc}
     */
    public function merge(CliPipelineCollectionInterface $pipelineCollection): void
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