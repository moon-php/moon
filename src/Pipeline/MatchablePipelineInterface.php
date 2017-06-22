<?php

declare(strict_types=1);

namespace Moon\Core\Pipeline;

use Moon\Core\Matchable\Matchable;

interface MatchablePipelineInterface extends PipelineInterface
{
    /**
     * Return true if a matchable Pipeline is matched, false otherwise
     *
     * @param Matchable $matchable
     *
     * @return bool
     */
    public function matchBy(Matchable $matchable): bool;
}