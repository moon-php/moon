<?php

declare(strict_types=1);

namespace Moon\Moon\Exception;

use Throwable;

class UnprocessableStageException extends InvalidArgumentException
{
    public const ERROR_MESSAGE = "The stage can't be handled. Given: %s";

    /**
     * @var string
     */
    private $stage;

    public function __construct($stage = null, int $code = 0, Throwable $previous = null)
    {
        $message = \sprintf(self::ERROR_MESSAGE, $this->castStageToString($stage));
        parent::__construct($message, $code, $previous);
        $this->stage = $stage;
    }

    public function getStage()
    {
        return $this->stage;
    }

    private function castStageToString($stage): string
    {
        if (\is_scalar($stage)) {
            return (string) $stage;
        }

        if (\is_array($stage)) {
            return \json_encode($stage) ?: 'array';
        }

        if (\is_object($stage)) {
            return \get_class($stage);
        }

        return \json_encode($stage) ?: 'Closure';
    }
}
