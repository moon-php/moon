<?php

declare(strict_types=1);

namespace Moon\Moon\Pipeline;

abstract class AbstractPipeline
{
    /**
     * @var array
     */
    protected $stages = [];

    /**
     * Add a stage to the Pipeline.
     *
     * @param callable|string|PipelineInterface|array $stages
     */
    public function pipe($stages): void
    {
        if ($stages instanceof PipelineInterface) {
            $stages = $stages->stages();
        }

        if (!\is_array($stages)) {
            $this->stages[] = $stages;

            return;
        }

        foreach ($stages as $stage) {
            $this->pipe($stage);
        }
    }

    public function stages(): array
    {
        return $this->stages;
    }
}
