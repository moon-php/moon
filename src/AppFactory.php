<?php

declare(strict_types=1);

namespace Moon\Moon;

use Moon\Moon\Exception\InvalidArgumentException;
use Moon\Moon\Handler\ErrorHandler;
use Moon\Moon\Handler\ErrorHandlerInterface;
use Moon\Moon\Handler\InvalidRequestHandler;
use Moon\Moon\Handler\InvalidRequestHandlerInterface;
use Moon\Moon\Matchable\MatchableRequest;
use Moon\Moon\Matchable\MatchableRequestInterface;
use Moon\Moon\Processor\ProcessorInterface;
use Moon\Moon\Processor\WebProcessor;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AppFactory
{
    /**
     * String to use as container entry for $streamReadLength()
     */
    public const STREAM_READ_LENGTH = 'moon.streamReadLength';

    /**
     * Create an App using a container
     *
     * @param ContainerInterface $container
     *
     * @return App
     *
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws InvalidArgumentException
     */
    public static function buildFromContainer(ContainerInterface $container): App
    {
        return new App(
            self::request($container),
            self::response($container),
            self::processor($container),
            self::matchableRequest($container),
            self::errorHandler($container),
            self::invalidRequestHandler($container),
            self::streamReadLength($container)
        );
    }

    /**
     * Return an instance of the ServerRequestInterface
     *
     * @param ContainerInterface $container
     *
     * @return ServerRequestInterface
     *
     * @throws ContainerExceptionInterface
     * @throws InvalidArgumentException
     * @throws NotFoundExceptionInterface
     */
    private static function request(ContainerInterface $container): ServerRequestInterface
    {
        if (!$container->has(ServerRequestInterface::class)) {
            throw new InvalidArgumentException('Moon received an invalid request instance from the container');
        }

        return $container->get(ServerRequestInterface::class);
    }

    /**
     * Return an instance of the ResponseInterface
     *
     * @param ContainerInterface $container
     *
     * @return ResponseInterface
     *
     * @throws ContainerExceptionInterface
     * @throws InvalidArgumentException
     * @throws NotFoundExceptionInterface
     */
    private static function response(ContainerInterface $container): ResponseInterface
    {
        if (!$container->has(ResponseInterface::class)) {
            throw new InvalidArgumentException('Moon received an invalid response instance from the container');
        }

        return $container->get(ResponseInterface::class);
    }

    /**
     * Return an instance of the ProcessorInterface
     *
     * @param ContainerInterface $container
     *
     * @return ProcessorInterface
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private static function processor(ContainerInterface $container): ProcessorInterface
    {
        if (!$container->has(ProcessorInterface::class)) {

            return new WebProcessor($container);
        }

        return $container->get(ProcessorInterface::class);
    }

    /**
     * Return an instance of the ErrorHandlerInterface
     *
     * @param ContainerInterface $container
     *
     * @return ErrorHandlerInterface
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private static function errorHandler(ContainerInterface $container): ErrorHandlerInterface
    {
        if (!$container->has(ErrorHandlerInterface::class)) {

            return new ErrorHandler();
        }

        return $container->get(ErrorHandlerInterface::class);
    }

    /**
     * Return an instance of the NotFoundHandlerInterface
     *
     * @param ContainerInterface $container
     *
     * @return InvalidRequestHandlerInterface
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private static function invalidRequestHandler(ContainerInterface $container): InvalidRequestHandlerInterface
    {
        if (!$container->has(InvalidRequestHandlerInterface::class)) {

            return new InvalidRequestHandler();
        }

        return $container->get(InvalidRequestHandlerInterface::class);
    }

    /**
     * Return an instance of the MatchableInterface
     *
     * @param ContainerInterface $container
     *
     * @return MatchableRequestInterface
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private static function matchableRequest(ContainerInterface $container): MatchableRequestInterface
    {
        if (!$container->has(MatchableRequestInterface::class)) {

            return new MatchableRequest($container->get(ServerRequestInterface::class));
        }

        return $container->get(MatchableRequestInterface::class);
    }

    /**
     * Return the length for read the stream, if null is return all the body will be print
     *
     * @param ContainerInterface $container
     *
     * @return int|null
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private static function streamReadLength(ContainerInterface $container):? int
    {
        return $container->has(self::STREAM_READ_LENGTH) ? $container->get(self::STREAM_READ_LENGTH) : null;
    }
}