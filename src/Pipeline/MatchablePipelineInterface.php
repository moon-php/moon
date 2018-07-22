<?php

declare(strict_types=1);

namespace Moon\Moon\Pipeline;

use Moon\Moon\Matchable\MatchableRequestInterface;

interface MatchablePipelineInterface extends PipelineInterface
{
    /**
     * Return true if a matchable Pipeline is matched, false otherwise.
     */
    public function matchBy(MatchableRequestInterface $matchable): bool;
}
