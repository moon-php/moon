<?php

declare(strict_types=1);

namespace App\Core\Matchable;

use Psr\Http\Message\RequestInterface;

class RequestMatchable implements Matchable
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * RequestMatchable constructor.
     * @param RequestInterface $request
     */
    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    public function match(array $criteria): bool
    {
        // TODO implement real match
        return $criteria['verb'] === $this->request->getMethod() && $criteria['pattern'] === $this->request->getUri();
    }
}