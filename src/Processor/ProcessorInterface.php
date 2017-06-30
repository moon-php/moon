<?php

declare(strict_types=1);

namespace Moon\Core\Processor;

use Psr\Http\Message\ResponseInterface;

interface ProcessorInterface
{
    /**
     *
     *
     * @param array $stages
     * @param mixed $argument
     *
     * @return ResponseInterface|string|void
     */
    public function processStages(array $stages, $argument = null);
}