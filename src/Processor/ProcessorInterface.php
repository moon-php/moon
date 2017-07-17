<?php

declare(strict_types=1);

namespace Moon\Core\Processor;

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
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Moon\Core\Exception\UnprocessableStageException
     */
    public function processStages(array $stages, $payload);
}