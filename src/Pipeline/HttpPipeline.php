<?php

declare(strict_types=1);

namespace Moon\Core\Pipeline;

use App\Core\Matchable\Matchable;

class HttpPipeline implements MatchablePipelineInterface
{
    /**
     * @var string
     */
    private $pattern;
    /**
     * @var array
     */
    protected $stages = [];
    /**
     * @var string
     */
    private $verb;

    public function __construct(string $verb, string $pattern)
    {
        $this->verb = strtoupper($verb);
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
        if ($matchable->match(['verb' => $this->verb, 'pattern' => $this->pattern])) { // Implement matching logic here
            return true;
        }

        return false;
    }

    public function stages(): array
    {
        return $this->stages;
    }
}