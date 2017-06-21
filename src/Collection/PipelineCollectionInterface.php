<?php

declare(strict_types=1);

namespace Moon\Core\Collection;


use Moon\Core\Pipeline\PipelineInterface;

interface PipelineCollectionInterface extends \IteratorAggregate
{
    public function add(PipelineInterface $pipeline): void;

    public function addArray(array $pipelines): void;

    public function merge(PipelineCollectionInterface $pipelineCollection): void;

    public function toArray(): array;
}