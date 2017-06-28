<?php

declare(strict_types=1);

namespace Moon\Core\Matchable;

use Psr\Http\Message\RequestInterface;

class RequestMatchable implements Matchable
{
    /**
     * @var RequestInterface $request
     */
    protected $request;

    /**
     * @var bool
     */
    protected $patternMatched = false;

    /**
     * RequestMatchable constructor.
     *
     * @param RequestInterface $request
     */
    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     * TODO implement real match
     */
    public function match(array $criteria): bool
    {
        if ($criteria['pattern'] !== $this->request->getUri()) {

            return false;
        }
        $this->patternMatched = true;

        return $criteria['verb'] === $this->request->getMethod();
    }

    /**
     * Return true if a request match a valid pattern but an invalid verb, false otherwise
     *
     * @return bool
     */
    public function isPatternMatched(): bool
    {
        return $this->patternMatched;
    }
}