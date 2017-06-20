<?php

declare(strict_types=1);

namespace App\Core\Matchable;

class StringMatchable implements Matchable
{
    /**
     * @var string
     */
    private $pattern;

    public function __construct(string $pattern)
    {
        $this->pattern = $pattern;
    }

    public function match(array $criteria): bool
    {
        return $criteria[0] === $this->pattern;
    }
}