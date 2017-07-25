<?php

declare(strict_types=1);

namespace Moon\Moon\Pipeline;

use Moon\Moon\Matchable\MatchableInterface;

interface MatchablePipelineInterface extends PipelineInterface
{
    /**
     * Return true if a matchable Pipeline is matched, false otherwise
     *
     * @param MatchableInterface $matchable
     *
     * @return bool
     */
    public function matchBy(MatchableInterface $matchable): bool;
}