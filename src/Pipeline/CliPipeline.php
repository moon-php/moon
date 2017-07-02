<?php

declare(strict_types=1);

namespace Moon\Core\Pipeline;

use Moon\Core\Matchable\MatchableInterface;

class CliPipeline extends AbstractPipeline implements MatchablePipelineInterface
{
    /**
     * @var string $pattern
     */
    protected $pattern;

    /**
     * CliPipeline constructor.
     *
     * @param string $pattern
     * @param callable|string|PipelineInterface|array $stages
     */
    public function __construct(string $pattern, $stages = null)
    {
        $this->pattern = $pattern;
        if ($stages !== null) {
            $this->pipe($stages);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function matchBy(MatchableInterface $matchable): bool
    {
        // TODO Implement matching logic here
        if ($matchable->match([$this->pattern])) {

            return true;
        }

        return false;
    }
}