<?php

declare(strict_types=1);

namespace Moon\Core\Collection;


use Moon\Core\Pipeline\HttpPipeline;

interface HttpPipelineCollectionInterface extends \IteratorAggregate
{
    public function add(HttpPipeline $pipeline): void;

    public function addArray(array $pipelines): void;

    public function merge(HttpPipelineCollectionInterface $pipelineCollection): void;

    public function toArray(): array;
}