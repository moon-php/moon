<?php

declare(strict_types=1);

namespace Moon\Core\Pipeline;

interface PipelineInterface
{
    /**
     * Add a stage to the Pipeline
     *
     * @param callable|string|PipelineInterface|array $stage
     *
     * @return void
     */
    public function pipe($stage): void;

    /**
     * Return all the stage of a Pipeline
     *
     * @return array
     */
    public function stages(): array;
}