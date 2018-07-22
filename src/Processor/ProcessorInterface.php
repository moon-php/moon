<?php

declare(strict_types=1);

namespace Moon\Moon\Processor;

use Moon\Moon\Exception\UnprocessableStageException;

interface ProcessorInterface
{
    /**
     * @throws UnprocessableStageException
     */
    public function processStages(array $stages, $payload);
}
