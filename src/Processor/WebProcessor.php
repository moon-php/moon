<?php

declare(strict_types=1);

namespace Moon\Core\Processor;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Moon\Core\Exception\Exception;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class WebProcessor implements ProcessorInterface
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
     * @return ResponseInterface|string
     *
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Moon\Core\Exception\Exception
     */
    public function processStages(array $stages, $args = null)
    {
        // Take the stage to handle and the next one (if exists)
        $currentStage = array_shift($stages);
        $nextStage = current($stages);

        // If is a string get the instance in the container
        if (is_string($currentStage)) {
            $currentStage = $this->container->get($currentStage);
        }

        // If the current stage is a Delegate use it and return the Response
        if ($currentStage instanceof DelegateInterface) {

            return $this->handleDelegate($currentStage, $args);
        }

        // If the current stage is a Middleware and the next one is a Delegate use it and return the Response
        if ($currentStage instanceof MiddlewareInterface && in_array(DelegateInterface::class, class_implements($nextStage), true)) {

            // Use the next stage as Delegate
            if (is_string($nextStage)) {
                $nextStage = $this->container->get($nextStage);
            }

            return $this->handleMiddleware($currentStage, $nextStage, $args);
        }

        // If there's not next stage in the stack, return the result for this one
        if ($nextStage === false && is_callable($currentStage)) {

            return $currentStage($args ?: $this->container->get('moon.request'));
        }

        // Process the current stage, and proceed to the stack
        if (is_callable($currentStage)) {

            return $this->processStages($stages, $currentStage($args ?: $this->container->get('moon.request')));
        }

        throw new Exception("The stage '$currentStage' can't be handled");
    }

    /**
     * Return a ResponseInterface form a DelegateInterface
     *
     * @param DelegateInterface $delegate
     * @param ServerRequestInterface|mixed $args
     *
     * @return ResponseInterface
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    protected function handleDelegate(DelegateInterface $delegate, $args): ResponseInterface
    {
        // If $args is a instance of the request, use it for process the middleware
        // Otherwise use the one into the container
        if ($args instanceof ServerRequestInterface) {
            $request = $args;
        }

        // Return the response
        return $delegate->process($request ?? $this->container->get('moon.request'));
    }

    /**
     * Return a ResponseInterface from a MiddlewareInterface
     *
     * @param MiddlewareInterface $currentStage
     * @param DelegateInterface $delegate
     * @param ServerRequestInterface|mixed $args
     *
     * @return ResponseInterface
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    protected function handleMiddleware(MiddlewareInterface $currentStage, DelegateInterface $delegate, $args): ResponseInterface
    {
        // If $args is a instance of the request, use it for process the middleware
        // Otherwise use the one into the container
        if ($args instanceof ServerRequestInterface) {
            $request = $args;
        }

        // Return the response if there's no more stage to execute, otherwise continue
        return $currentStage->process($request ?? $this->container->get('moon.request'), $delegate);
    }
}