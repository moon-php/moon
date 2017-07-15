<?php

declare(strict_types=1);

namespace Moon\Core\Processor;

use Moon\Core\Exception\Exception;
use Psr\Container\ContainerInterface;

class CliProcessor implements ProcessorInterface
{
    /**
     * @var ContainerInterface $container
     */
    private $container;

    /**
     * WebProcessorInterface constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Handle all the passed stages
     *
     * @param array $stages
     * @param mixed $payload
     *
     * @return void
     *
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Moon\Core\Exception\Exception
     */
    public function processStages(array $stages, $payload): void
    {
        // Take the stage to handle and the next one (if exists)
        $currentStage = array_shift($stages);
        $nextStage = current($stages);

        if (is_string($currentStage)) {
            $currentStage = $this->container->get($currentStage);
        }

        if (!is_callable($currentStage)) {
            throw new Exception("The stage '$currentStage' can't be handled");
        }

        $payload = $currentStage($payload);

        if ($nextStage !== false) {
            $this->processStages($stages, $payload);
        }
    }
}