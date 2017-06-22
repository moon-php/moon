<?php

declare(strict_types=1);

namespace Moon\Core\Matchable;

interface Matchable
{
    /**
     * Return true if a matchable object is matched by criteria
     *
     * @param array $criteria
     *
     * @return bool
     */
    public function match(array $criteria): bool;
}