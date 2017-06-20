<?php

declare(strict_types=1);

namespace App\Core\Matchable;

interface Matchable
{
    public function match(array $criteria): bool;
}