<?php

declare(strict_types=1);

namespace Moon\Core\Pipeline;


interface PipelineInterface
{
    public function pipe($stage): void;

    public function stages(): array;
}