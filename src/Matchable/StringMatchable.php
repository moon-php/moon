<?php

declare(strict_types=1);

namespace Moon\Core\Matchable;

class StringMatchable implements MatchableInterface
{
    /**
     * @var string $pattern
     */
    protected $pattern;

    /**
     * StringMatchable constructor.
     *
     * @param string $pattern
     */
    public function __construct(string $pattern)
    {
        $this->pattern = $pattern;
    }

    /**
     * {@inheritdoc}
     */
    public function match(array $criteria): bool
    {
        return $criteria[0] === $this->pattern;
    }
}