<?php

declare(strict_types=1);

namespace Moon\Core\Pipeline;

use Moon\Core\Exception\InvalidArgumentException;
use Moon\Core\Matchable\MatchableInterface;

class HttpPipeline extends AbstractPipeline implements MatchablePipelineInterface
{
    /**
     * @var string $pattern
     */
    protected $pattern;

    /**
     * @var string $verb
     */
    protected $verb;

    /**
     * @var array
     */
    protected const VALID_VERBS = ['GET', 'PUT', 'PATCH', 'POST', 'DELETE', 'OPTIONS', 'HEAD'];

    /**
     * HttpPipeline constructor.
     *
     * @param string $verb
     * @param string $pattern
     * @param callable|string|PipelineInterface|array $stages
     *
     * @throws \Moon\Core\Exception\InvalidArgumentException
     */
    public function __construct(string $verb, string $pattern, $stages = null)
    {
        $verb = strtoupper($verb);
        if (!in_array($verb, self::VALID_VERBS, true)) {
            throw new InvalidArgumentException("The verb: $verb is not a valid http verb");
        }
        $this->verb = $verb;
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
        if ($matchable->match(['verb' => $this->verb, 'pattern' => $this->pattern])) {
            
            return true;
        }

        return false;
    }
}