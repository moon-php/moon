<?php

declare(strict_types=1);

namespace Moon\Moon\Collection;

use Moon\Moon\Pipeline\MatchablePipelineInterface;

interface MatchablePipelineCollectionInterface extends \IteratorAggregate
{
    /**
     * Add a MatchablePipelineInterface to the Collection.
     */
    public function add(MatchablePipelineInterface $pipeline): void;

    /**
     * Add an array of MatchablePipelineInterface to the Collection.
     */
    public function addArray(array $pipelines): void;

    /**
     * Merge a MatchablePipelineInterfaceCollection.
     */
    public function merge(self $pipelineCollection): void;

    /**
     * Return an array of MatchablePipelineInterfaces.
     */
    public function toArray(): array;
}
