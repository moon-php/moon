<?php

declare(strict_types=1);

namespace Moon\Moon\Collection;

use Moon\Moon\Pipeline\PipelineInterface;

interface PipelineCollectionInterface extends \IteratorAggregate
{
    /**
     * Add a PipelineInterface to the Collection
     *
     * @param PipelineInterface $pipeline
     *
     * @return void
     */
    public function add(PipelineInterface $pipeline): void;

    /**
     * Add an array of PipelineInterface to the Collection
     *
     * @param array $pipelines
     *
     * @return void
     */
    public function addArray(array $pipelines): void;

    /**
     * Merge a PipelineInterfaceCollection
     *
     * @param PipelineCollectionInterface $pipelineCollection
     *
     * @return void
     */
    public function merge(PipelineCollectionInterface $pipelineCollection): void;

    /**
     * Return an array of PipelineInterfaces
     *
     * @return array
     */
    public function toArray(): array;
}