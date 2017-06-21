<?php

declare(strict_types=1);

namespace Moon\Core\Pipeline;

abstract class AbstractPipeline
{
    /**
     * @var array
     */
    protected $stages = [];

    public function pipe($stages): void
    {
        if (!is_array($stages)) {
            $this->stages[] = $stages;
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