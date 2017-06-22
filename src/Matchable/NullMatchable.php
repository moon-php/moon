<?php

declare(strict_types=1);

namespace Moon\Core\Matchable;

class NullMatchable implements Matchable
{
    /**
     * {@inheritdoc}
     */
    public function match(array $criteria): bool
    {
        return true;
    }
}