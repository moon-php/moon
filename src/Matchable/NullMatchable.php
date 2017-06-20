<?php

declare(strict_types=1);

namespace App\Core\Matchable;

class NullMatchable implements Matchable
{
    public function match(array $criteria): bool
    {
        return true;
    }
}