<?php

declare(strict_types=1);

namespace Moon\Core\Pipeline;

class ApplicationPipeline extends AbstractPipeline implements PipelineInterface
{
    public function __construct($stages = null)
    {
        if ($stages !== null) {
            $this->pipe($stages);
        }
    }
}