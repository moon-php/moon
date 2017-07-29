<?php

declare(strict_types=1);

namespace Moon\Moon\Container;

use Moon\Moon\Handler\Error\ExceptionHandlerInterface;
use Moon\Moon\Handler\Error\ThrowableHandlerInterface;
use Moon\Moon\Handler\InvalidRequest\MethodNotAllowedHandlerInterface;
use Moon\Moon\Handler\InvalidRequest\NotFoundHandlerInterface;
use Moon\Moon\Matchable\MatchableInterface;
use Moon\Moon\Processor\ProcessorInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;

interface ContainerWrapperInterface
{
    /**
     * ContainerWrapperInterface constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container);

    /**
     * Return an instance of the ServerRequestInterface
     *
     * @return ServerRequestInterface
     *
     * @throws \Moon\Moon\Exception\InvalidArgumentException
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function request(): ServerRequestInterface;

    /**
     * Return an instance of the ResponseInterface
     *
     * @return ResponseInterface
     *
     * @throws \Moon\Moon\Exception\InvalidArgumentException
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function response(): ResponseInterface;

    /**
     * Return an instance of the ProcessorInterface
     *
     * @return ProcessorInterface
     *
     * @throws \Moon\Moon\Exception\InvalidArgumentException
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function processor(): ProcessorInterface;

    /**
     * Return an instance of the ExceptionHandlerInterface
     *
     * @return ExceptionHandlerInterface
     *
     * @throws \Moon\Moon\Exception\InvalidArgumentException
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function exceptionHandler(): ExceptionHandlerInterface;

    /**
     * Return an instance of the ThrowableHandlerInterface
     *
     * @return ThrowableHandlerInterface
     *
     * @throws \Moon\Moon\Exception\InvalidArgumentException
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function throwableHandler(): ThrowableHandlerInterface;

    /**
     * Return an instance of the NotFoundHandlerInterface
     *
     * @return NotFoundHandlerInterface
     *
     * @throws \Moon\Moon\Exception\InvalidArgumentException
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function notFoundHandler(): NotFoundHandlerInterface;

    /**
     * Return an instance of the MethodNotAllowedHandlerInterface
     *
     * @return MethodNotAllowedHandlerInterface
     *
     * @throws \Moon\Moon\Exception\InvalidArgumentException
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function methodNotAllowed(): MethodNotAllowedHandlerInterface;

    /**
     * Return an instance of the MatchableInterface
     *
     * @return MatchableInterface
     *
     * @throws \Moon\Moon\Exception\InvalidArgumentException
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function matchableRequest(): MatchableInterface;

    /**
     * Return an instance of the StreamInterface
     *
     * @return StreamInterface
     *
     * @throws \Moon\Moon\Exception\InvalidArgumentException
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function stream(): StreamInterface;

    /**
     * Return the length for read the stream, if null is return all the body will be print
     *
     * @return int|null
     */
    public function streamReadLength():? int;
}