<?php

declare(strict_types=1);

namespace Moon\Core\Processor;

use Moon\Core\Command\CommandInterface;
use Moon\Core\Exception\UnprocessableStageException;
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
     * {@inheritdoc}
     *
     * @return void
     */
    public function processStages(array $stages, $payload): void
    {
        // Take the stage to handle and the next one (if exists)
        $currentStage = array_shift($stages);
        $nextStage = current($stages);

        if (is_string($currentStage)) {
            $currentStage = $this->container->get($currentStage);
        }

        if ($currentStage instanceof CommandInterface) {
            $currentStage->configure();
        }

        if (!is_callable($currentStage)) {
            throw new UnprocessableStageException($currentStage, 'The stage can\'t be handled');
        }

        $payload = $currentStage($payload);

        if ($nextStage !== false) {
            $this->processStages($stages, $payload);
        }
    }
}