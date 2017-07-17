<?php

declare(strict_types=1);

namespace Moon\Core\Pipeline;

use Fig\Http\Message\RequestMethodInterface;
use Moon\Core\Exception\InvalidArgumentException;
use Moon\Core\Matchable\MatchableInterface;

class HttpPipeline extends AbstractPipeline implements MatchablePipelineInterface
{
    /**
     * @var array VALID_VERBS
     */
    private const VALID_VERBS = [
        RequestMethodInterface::METHOD_HEAD,
        RequestMethodInterface::METHOD_GET,
        RequestMethodInterface::METHOD_POST,
        RequestMethodInterface::METHOD_PUT,
        RequestMethodInterface::METHOD_PATCH,
        RequestMethodInterface::METHOD_DELETE,
        RequestMethodInterface::METHOD_PURGE,
        RequestMethodInterface::METHOD_OPTIONS,
        RequestMethodInterface::METHOD_TRACE,
        RequestMethodInterface::METHOD_CONNECT,
    ];

    /**
     * @var string $pattern
     */
    private $pattern;

    /**
     * @var array $verbs
     */
    private $verbs;

    /**
     * HttpPipeline constructor.
     *
     * @param array|string $verbs
     * @param string $pattern
     * @param callable|string|PipelineInterface|array $stages
     *
     * @throws \Moon\Core\Exception\InvalidArgumentException
     */
    public function __construct($verbs, string $pattern, $stages = null)
    {
        if (!is_array($verbs)) {
            $verbs = [$verbs];
        }

        /** @var array $verbs */
        foreach ($verbs as $k => $verb) {
            $verbs[$k] = strtoupper($verb);
            if (!in_array($verbs[$k], self::VALID_VERBS, true)) {
                throw new InvalidArgumentException("The verb: {$verbs[$k]} is not a valid http verb");
            }
        }

        $this->verbs = $verbs;
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
        if ($matchable->match(['verbs' => $this->verbs, 'pattern' => $this->pattern])) {

            return true;
        }

        return false;
    }
}