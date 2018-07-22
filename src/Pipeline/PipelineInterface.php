<?php

declare(strict_types=1);

namespace Moon\Moon\Pipeline;

interface PipelineInterface
{
    /**
     * Add a stage to the Pipeline.
     *
     * @param callable|string|PipelineInterface|array $stage
     */
    public function pipe($stage): void;

    /**
     * Return all the stage of a Pipeline.
     */
    public function stages(): array;
}
