<?php

declare(strict_types=1);

namespace Moon\Moon\Matchable;

use Psr\Http\Message\ServerRequestInterface;

interface MatchableRequestInterface
{
    /**
     * Return true if a matchable object is matched by criteria.
     *
     * @param array $criteria
     *
     * @return bool
     */
    public function match(array $criteria): bool;

    /**
     * Return the new ServerRequest with the added attributes.
     *
     * @return ServerRequestInterface
     */
    public function request(): ServerRequestInterface;

    /**
     * Return true if a request match a valid pattern but an invalid verb, false otherwise.
     *
     * @return bool
     */
    public function isPatternMatched(): bool;
}
