<?php

declare(strict_types=1);

namespace Moon\Moon\Container;

use Moon\Moon\Exception\InvalidArgumentException;
use Moon\Moon\Handler\Error\ExceptionHandler;
use Moon\Moon\Handler\Error\ExceptionHandlerInterface;
use Moon\Moon\Handler\Error\ThrowableHandler;
use Moon\Moon\Handler\Error\ThrowableHandlerInterface;
use Moon\Moon\Handler\InvalidRequest\MethodNotAllowedHandler;
use Moon\Moon\Handler\InvalidRequest\MethodNotAllowedHandlerInterface;
use Moon\Moon\Handler\InvalidRequest\NotFoundHandler;
use Moon\Moon\Handler\InvalidRequest\NotFoundHandlerInterface;
use Moon\Moon\Matchable\MatchableInterface;
use Moon\Moon\Matchable\RequestMatchable;
use Moon\Moon\Processor\ProcessorInterface;
use Moon\Moon\Processor\WebProcessor;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;

class ContainerWrapper implements ContainerWrapperInterface
{
    /**
     * String to use as container entry for $this->streamReadLength()
     */
    public const STREAM_READ_LENGTH = 'moon.streamReadLength';

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritdoc}
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function request(): ServerRequestInterface
    {
        if (!$this->container->has(ServerRequestInterface::class)) {
            throw new InvalidArgumentException('Moon received an invalid request instance from the container');
        }

        return $this->container->get(ServerRequestInterface::class);
    }

    /**
     * {@inheritdoc}
     */
    public function response(): ResponseInterface
    {
        if (!$this->container->has(ResponseInterface::class)) {
            throw new InvalidArgumentException('Moon received an invalid response instance from the container');
        }

        return $this->container->get(ResponseInterface::class);
    }

    /**
     * {@inheritdoc}
     */
    public function stream(): StreamInterface
    {
        if (!$this->container->has(StreamInterface::class)) {
            throw new InvalidArgumentException('Moon received an invalid stream instance from the container');
        }

        return $this->container->get(StreamInterface::class);
    }

    /**
     * {@inheritdoc}
     */
    public function processor(): ProcessorInterface
    {
        if (!$this->container->has(ProcessorInterface::class)) {

            return new WebProcessor($this->container);
        }

        return $this->container->get(ProcessorInterface::class);
    }

    /**
     * {@inheritdoc}
     */
    public function exceptionHandler(): ExceptionHandlerInterface
    {
        if (!$this->container->has(ExceptionHandlerInterface::class)) {

            return new ExceptionHandler();
        }

        return $this->container->get(ExceptionHandlerInterface::class);
    }

    /**
     * {@inheritdoc}
     */
    public function throwableHandler(): ThrowableHandlerInterface
    {
        if (!$this->container->has(ThrowableHandlerInterface::class)) {

            return new ThrowableHandler();
        }

        return $this->container->get(ThrowableHandlerInterface::class);
    }

    /**
     * {@inheritdoc}
     */
    public function notFoundHandler(): NotFoundHandlerInterface
    {
        if (!$this->container->has(NotFoundHandlerInterface::class)) {

            return new NotFoundHandler();
        }

        return $this->container->get(NotFoundHandlerInterface::class);
    }

    /**
     * {@inheritdoc}
     */
    public function methodNotAllowed(): MethodNotAllowedHandlerInterface
    {
        if (!$this->container->has(MethodNotAllowedHandlerInterface::class)) {

            return new MethodNotAllowedHandler();
        }

        return $this->container->get(MethodNotAllowedHandlerInterface::class);
    }

    /**
     * {@inheritdoc}
     */
    public function matchableRequest(): MatchableInterface
    {
        if (!$this->container->has(MatchableInterface::class)) {

            return new RequestMatchable($this->request());
        }

        return $this->container->get(MatchableInterface::class);
    }

    /**
     * {@inheritdoc}
     */
    public function streamReadLength():? int
    {
        return $this->container->has(self::STREAM_READ_LENGTH) ? $this->container->get(self::STREAM_READ_LENGTH) : null;
    }
}