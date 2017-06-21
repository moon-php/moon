<?php

declare(strict_types=1);

namespace Moon\Core\Pipeline;

use App\Core\Matchable\Matchable;

class HttpPipeline extends AbstractPipeline implements MatchablePipelineInterface
{
    /**
     * @var string
     */
    private $pattern;
    /**
     * @var string
     */
    private $verb;

    public function __construct(string $verb, string $pattern, $stages = null)
    {
        $this->verb = strtoupper($verb);
        $this->pattern = $pattern;
        if ($stages !== null) {
            $this->pipe($stages);
        }
    }

    public function matchBy(Matchable $matchable): bool
    {
        if ($matchable->match(['verb' => $this->verb, 'pattern' => $this->pattern])) { // Implement matching logic here
            return true;
        }

        return false;
    }
}