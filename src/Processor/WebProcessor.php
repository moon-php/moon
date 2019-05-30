<?php

declare(strict_types=1);

namespace Moon\Moon\Processor;

use function current;
use Moon\Moon\Exception\UnprocessableStageException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

class WebProcessor implements ProcessorInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     *
     * @return ResponseInterface|string
     */
    public function processStages(array $stages, $payload)
    {
        // Take the stage to handle
        $currentStage = \array_shift($stages);

        // If is a string get the instance in the container
        if (\is_string($currentStage) && $this->container->has($currentStage)) {
            $currentStage = $this->container->get($currentStage);
        }

        // If the current stage is a RequestHandler use it and return the Response
        if ($currentStage instanceof RequestHandlerInterface) {
            return $currentStage->handle($payload);
        }

        // Process the current stage, and proceed to the stack
        if (false !== \current($stages) && \is_callable($currentStage)) {
            return $this->processStages($stages, $currentStage($payload));
        }

        // If there's not next stage in the stack, return the result for this one
        if (\is_callable($currentStage)) {
            return $currentStage($payload);
        }

        throw new UnprocessableStageException($currentStage);
    }
}
