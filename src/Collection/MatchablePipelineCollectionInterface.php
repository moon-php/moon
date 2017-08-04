<?php

declare(strict_types=1);

namespace Moon\Moon\Collection;

use Moon\Moon\Pipeline\MatchablePipelineInterface;

interface MatchablePipelineCollectionInterface extends \IteratorAggregate
{
    /**
     * Add a MatchablePipelineInterface to the Collection
     *
     * @param MatchablePipelineInterface $pipeline
     *
     * @return void
     */
    public function add(MatchablePipelineInterface $pipeline): void;

    /**
     * Add an array of MatchablePipelineInterface to the Collection
     *
     * @param array $pipelines
     *
     * @return void
     */
    public function addArray(array $pipelines): void;

    /**
     * Merge a MatchablePipelineInterfaceCollection
     *
     * @param MatchablePipelineCollectionInterface $pipelineCollection
     *
     * @return void
     */
    public function merge(MatchablePipelineCollectionInterface $pipelineCollection): void;

    /**
     * Return an array of MatchablePipelineInterfaces
     *
     * @return array
     */
    public function toArray(): array;
}