<?php

declare(strict_types=1);

namespace Moon\Core\Pipeline;

abstract class AbstractPipeline
{
    /**
     * @var array $stages
     */
    protected $stages = [];

    /**
     * Add a stage to the Pipeline
     *
     * @param callable|string|PipelineInterface|array $stages
     *
     * @return void
     */
    public function pipe($stages): void
    {
        if ($stages instanceof PipelineInterface) {
            $stages = $stages->stages();
        }

        if (!is_array($stages)) {
            $this->stages[] = $stages;

            return;
        }

        foreach ($stages as $stage) {
            $this->pipe($stage);
        }
    }

    /**
     * @return array
     */
    public function stages(): array
    {
        return $this->stages;
    }
}