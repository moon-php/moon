<?php

declare(strict_types=1);

namespace Moon\Moon\Processor;

use Moon\Moon\Exception\UnprocessableStageException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

interface ProcessorInterface
{
    /**
     * Handle all the passed stages
     *
     * @param array $stages
     * @param mixed $payload
     *
     * @return mixed
     *
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws UnprocessableStageException
     */
    public function processStages(array $stages, $payload);
}