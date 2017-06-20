<?php

declare(strict_types=1);

namespace Moon\Core\Pipeline;

use App\Core\Matchable\Matchable;

class CliPipeline implements MatchablePipelineInterface
{
    /**
     * @var array
     */
    protected $stages = [];
    /**
     * @var string
     */
    protected $pattern;

    public function __construct(string $pattern)
    {
        $this->pattern = $pattern;
    }

    public function pipe($stages): void
    {
        if (!is_array($stages)) {
            $this->stages[] = $stages;
        }

        foreach ($stages as $stage) {
            $this->pipe($stage);
        }
    }

    public function matchBy(Matchable $matchable): bool
    {
        if ($matchable->match([$this->pattern])) { // Implement matching logic here
            return true;
        }

        return false;
    }

    public function stages(): array
    {
        return $this->stages;
    }
}