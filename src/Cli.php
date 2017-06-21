<?php

declare(strict_types=1);

namespace Moon\Core;

use Moon\Core\Collection\CliPipelineArrayCollection;
use Moon\Core\Collection\CliPipelineCollectionInterface;
use Moon\Core\Pipeline\CliPipeline;

class Cli
{
    private $cliPipelines = [];

    public function command(string $pattern, array $stages): void
    {
        $cliPipeline = new CliPipeline($pattern);
        $cliPipeline->pipe($stages);
        $this->cliPipelines[] = $cliPipeline;
    }

    public function pipelines(): CliPipelineCollectionInterface
    {
        return new CliPipelineArrayCollection($this->cliPipelines);
    }
}