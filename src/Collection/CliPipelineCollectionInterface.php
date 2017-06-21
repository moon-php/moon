<?php

declare(strict_types=1);

namespace Moon\Core\Collection;


use Moon\Core\Pipeline\CliPipeline;

interface CliPipelineCollectionInterface extends \IteratorAggregate
{
    public function add(CliPipeline $pipeline): void;

    public function addArray(array $pipelines): void;

    public function merge(CliPipelineCollectionInterface $pipelineCollection): void;

    public function toArray(): array;
}