<?php

declare(strict_types=1);

namespace Moon\Core\Collection;

use Moon\Core\Pipeline\HttpPipeline;

interface HttpPipelineCollectionInterface extends \IteratorAggregate
{
    /**
     * Add a HttpPipeline to the Collection
     *
     * @param HttpPipeline $pipeline
     *
     * @return void
     */
    public function add(HttpPipeline $pipeline): void;

    /**
     * Add an array of Pipeline to the Collection
     *
     * @param array $pipelines
     *
     * @return void
     */
    public function addArray(array $pipelines): void;

    /**
     * Merge a HttpPipelineCollection
     *
     * @param HttpPipelineCollectionInterface $pipelineCollection
     *
     * @return void
     */
    public function merge(HttpPipelineCollectionInterface $pipelineCollection): void;

    /**
     * Return an array of Pipelines
     *
     * @return array
     */
    public function toArray(): array;
}