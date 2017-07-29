<?php

declare(strict_types=1);

namespace Moon\Moon\Matchable;

use Psr\Http\Message\ServerRequestInterface;
use function array_pop;
use function array_shift;
use function in_array;
use function is_int;
use function preg_match;
use function preg_match_all;
use function preg_replace;
use function preg_replace_callback;
use function strpos;
use function substr;

class RequestMatchable implements MatchableInterface
{
    private const REGEX_PREFIX = '::';
    private const REQUIRED_PLACEHOLDER_REGEX = '~\{(.*?)\}~';
    private const OPTIONAL_PLACEHOLDER_REGEX = '~\[((?>[^\[\]]+))*\]~';

    /**
     * @var ServerRequestInterface $request
     */
    private $request;

    /**
     * @var bool $patternMatched
     */
    private $patternMatched = false;

    /**
     * RequestMatchable constructor.
     *
     * @param ServerRequestInterface $request
     */
    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function match(array $criteria): bool
    {
        // Check if is a regex or transform it
        if (strpos($criteria['pattern'], self::REGEX_PREFIX) === 0) {
            $regex = substr($criteria['pattern'], 2);
        } else {
            $regex = $this->toRegex($criteria['pattern']);
        }

        /** @var bool $isPatternMatched */
        /** @var array $matches */
        [$isPatternMatched, $matches] = $this->matchByRegex($regex, $this->request->getUri()->getPath());

        if (!$isPatternMatched) {
            return false;
        }

        $this->patternMatched = true;

        if (!in_array($this->request->getMethod(), $criteria['verbs'], true)) {
            return false;
        }

        foreach ($matches as $name => $value) {
            $this->request = $this->request->withAttribute($name, $value[0]);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isPatternMatched(): bool
    {
        return $this->patternMatched;
    }

    /**
     * {@inheritdoc}
     */
    public function requestWithAddedAttributes(): ServerRequestInterface
    {
        return $this->request;
    }

    /**
     * Return an array made by 2 elements:
     *  - A boolean value as value for know if the current pattern matches the path
     *  - An array with key/value as element to add to the request
     *
     * @param string $pattern
     * @param string $path
     *
     * @return array
     */
    private function matchByRegex(string $pattern, string $path): array
    {
        $isPatternMatched = (bool)preg_match_all("~^$pattern$~", $path, $matches);

        if (!$isPatternMatched) {
            return [$isPatternMatched, []];
        }

        foreach (array_shift($matches) as $k => $match) {
            if (is_int($k)) {
                unset($matches[$k]);
                continue;
            }

            $matches[$k] = array_shift($match);
        }

        return [$isPatternMatched, $matches];
    }

    /**
     * Transform a pattern into a regex
     *
     * @param string $pattern
     *
     * @return string
     */
    private function toRegex(string $pattern): string
    {
        while (preg_match(self::OPTIONAL_PLACEHOLDER_REGEX, $pattern)) {
            $pattern = preg_replace(self::OPTIONAL_PLACEHOLDER_REGEX, '($1)?', $pattern);
        }

        $pattern = preg_replace_callback(self::REQUIRED_PLACEHOLDER_REGEX, function (array $match = []) {

            $match = array_pop($match);
            $pos = strpos($match, self::REGEX_PREFIX);
            if ($pos !== false) {
                $parameterName = substr($match, 0, $pos);
                $parameterRegex = substr($match, $pos + 2);

                return "(?<$parameterName>$parameterRegex)";
            }

            return "(?<$match>[^/]+)";
        }, $pattern);

        return $pattern;
    }
}