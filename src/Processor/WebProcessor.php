<?php

declare(strict_types=1);

namespace Moon\Moon\Processor;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Moon\Moon\Exception\UnprocessableStageException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use function array_shift;
use function class_implements;
use function current;
use function in_array;
use function is_callable;
use function is_string;

class WebProcessor implements ProcessorInterface
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
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     *
     * @return ResponseInterface|string
     */
    public function processStages(array $stages, $payload)
    {
        // Take the stage to handle and the next one (if exists)
        $currentStage = array_shift($stages);
        $nextStage = current($stages);

        // If is a string get the instance in the container
        if (is_string($currentStage) && $this->container->has($currentStage)) {
            $currentStage = $this->container->get($currentStage);
        }

        // If the current stage is a Delegate use it and return the Response
        if ($currentStage instanceof DelegateInterface) {

            return $currentStage->process($payload);
        }

        // If the current stage is a Middleware and the next one is a Delegate, use it and return the Response
        if ($currentStage instanceof MiddlewareInterface) {

            // Use the next stage as Delegate
            if (is_string($nextStage) && $this->container->has($nextStage)) {
                $nextStage = $this->container->get($nextStage);
            }

            if (in_array(DelegateInterface::class, class_implements($nextStage), true)) {
                return $currentStage->process($payload, $nextStage);
            }
        }

        // Process the current stage, and proceed to the stack
        if ($nextStage !== false && is_callable($currentStage)) {

            return $this->processStages($stages, $currentStage($payload));
        }

        // If there's not next stage in the stack, return the result for this one
        if (is_callable($currentStage)) {

            return $currentStage($payload);
        }

        throw new UnprocessableStageException($currentStage, 'The stage can\'t be handled');
    }
}