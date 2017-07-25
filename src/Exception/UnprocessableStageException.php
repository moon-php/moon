<?php

declare(strict_types=1);

namespace Moon\Moon\Exception;

use Throwable;

class UnprocessableStageException extends \InvalidArgumentException
{
    /**
     * @var mixed
     */
    private $stage;

    public function __construct($stage = '', $message = '', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->stage = $stage;
    }

    public function getStage()
    {
        return $this->stage;
    }
}