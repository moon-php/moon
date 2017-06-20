<?php

declare(strict_types=1);

namespace Moon\Core\Pipeline;


use App\Core\Matchable\Matchable;

interface MatchablePipelineInterface extends PipelineInterface
{
    public function matchBy(Matchable $matchable): bool;
}