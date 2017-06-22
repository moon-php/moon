<?php

declare(strict_types=1);

namespace Moon\Core\Collection;

use Moon\Core\Pipeline\CliPipeline;

interface CliPipelineCollectionInterface extends \IteratorAggregate
{
    /**
     * Add a CliPipeline to the Collection
     *
     * @param CliPipeline $pipeline
     *
     * @return void
     */
    public function add(CliPipeline $pipeline): void;

    /**
     * Add an array of Pipeline to the Collection
     *
     * @param array $pipelines
     *
     * @return void
     */
    public function addArray(array $pipelines): void;

    /**
     * Merge a CliPipelineCollection
     *
     * @param CliPipelineCollectionInterface $pipelineCollection
     *
     * @return void
     */
    public function merge(CliPipelineCollectionInterface $pipelineCollection): void;

    /**
     * Return an array of Pipelines
     *
     * @return array
     */
    public function toArray(): array;
}