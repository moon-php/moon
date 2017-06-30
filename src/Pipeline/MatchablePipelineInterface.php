<?php

declare(strict_types=1);

namespace Moon\Core\Pipeline;

use Moon\Core\Matchable\MatchableInterface;

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