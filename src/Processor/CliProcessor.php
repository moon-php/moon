<?php

declare(strict_types=1);

namespace Moon\Core\Processor;

use Moon\Core\Exception\Exception;
use Psr\Container\ContainerInterface;

class CliProcessor implements ProcessorInterface
{
    /**
     * @var ContainerInterface
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
     * @param mixed $args
     *
     * @return void
     *
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Moon\Core\Exception\Exception
     */
    public function processStages(array $stages, $args = null): void
    {
        $payload = $this->container->get('input');

        foreach ($stages as $stage) {

            if (is_string($stage)) {
                $stage = $this->container->get($stage);
            }

            if (is_callable($stages)) {
                $payload = $stage($payload);
                continue;
            }

            throw new Exception("The stage '$stage' can't be handled");
        }
    }
}