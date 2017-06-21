<?php

declare(strict_types=1);

namespace Moon\Core\Pipeline;

use App\Core\Matchable\Matchable;

class CliPipeline extends AbstractPipeline implements MatchablePipelineInterface
{
    /**
     * @var string
     */
    protected $pattern;

    public function __construct(string $pattern, $stages = null)
    {
        $this->pattern = $pattern;
        if ($stages !== null) {
            $this->pipe($stages);
        }
    }

    public function matchBy(Matchable $matchable): bool
    {
        if ($matchable->match([$this->pattern])) { // Implement matching logic here
            return true;
        }

        return false;
    }
}