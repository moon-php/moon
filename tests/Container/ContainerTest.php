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
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use ReflectionProperty;

class ContainerTest extends TestCase
{
    public function testConstruct()
    {
        $container = $this->prophesize(ContainerInterface::class)->reveal();
        $containerWrapper = new ContainerWrapper($container);

        $reflection = new ReflectionProperty(ContainerWrapper::class, 'container');
        $reflection->setAccessible(true);
        $containerSetInWrapper = $reflection->getValue($containerWrapper);
        $this->assertSame($containerSetInWrapper, $container);
    }

    public function testRequest()
    {
        $request = $this->prophesize(ServerRequestInterface::class)->reveal();
        $container = $this->prophesize(ContainerInterface::class);
        $container->has(ServerRequestInterface::class)->shouldBeCalled(1)->willReturn(true);
        $container->get(ServerRequestInterface::class)->shouldBeCalled(1)->willReturn($request);
        $containerWrapper = new ContainerWrapper($container->reveal());

        $this->assertSame($request, $containerWrapper->request());
    }

    public function testRequestNotFound()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Moon received an invalid request instance from the container');

        $container = $this->prophesize(ContainerInterface::class);
        $container->has(ServerRequestInterface::class)->shouldBeCalled(1)->willReturn(false);
        $containerWrapper = new ContainerWrapper($container->reveal());

        $containerWrapper->request();
    }

    public function testResponse()
    {
        $response = $this->prophesize(ResponseInterface::class)->reveal();
        $container = $this->prophesize(ContainerInterface::class);
        $container->has(ResponseInterface::class)->shouldBeCalled(1)->willReturn(true);
        $container->get(ResponseInterface::class)->shouldBeCalled(1)->willReturn($response);
        $containerWrapper = new ContainerWrapper($container->reveal());

        $this->assertSame($response, $containerWrapper->response());
    }

    public function testResponseNotFound()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Moon received an invalid response instance from the container');

        $container = $this->prophesize(ContainerInterface::class);
        $container->has(ResponseInterface::class)->shouldBeCalled(1)->willReturn(false);
        $containerWrapper = new ContainerWrapper($container->reveal());

        $containerWrapper->response();
    }

    public function testDefaultProcessorIsReturned()
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->has(ProcessorInterface::class)->shouldBeCalled(1)->willReturn(false);
        $containerWrapper = new ContainerWrapper($container->reveal());

        $this->assertInstanceOf(WebProcessor::class, $containerWrapper->processor());
    }

    public function testProcessor()
    {
        $processor = $this->prophesize(ProcessorInterface::class)->reveal();
        $container = $this->prophesize(ContainerInterface::class);
        $container->has(ProcessorInterface::class)->shouldBeCalled(1)->willReturn(true);
        $container->get(ProcessorInterface::class)->shouldBeCalled(1)->willReturn($processor);
        $containerWrapper = new ContainerWrapper($container->reveal());

        $this->assertSame($processor, $containerWrapper->processor());
    }

    public function testDefaultExceptionHandlerIsReturned()
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->has(ExceptionHandlerInterface::class)->shouldBeCalled(1)->willReturn(false);
        $containerWrapper = new ContainerWrapper($container->reveal());

        $this->assertInstanceOf(ExceptionHandler::class, $containerWrapper->exceptionHandler());
    }

    public function testExceptionHandler()
    {
        $exceptionHandler = $this->prophesize(ExceptionHandlerInterface::class)->reveal();
        $container = $this->prophesize(ContainerInterface::class);
        $container->has(ExceptionHandlerInterface::class)->shouldBeCalled(1)->willReturn(true);
        $container->get(ExceptionHandlerInterface::class)->shouldBeCalled(1)->willReturn($exceptionHandler);
        $containerWrapper = new ContainerWrapper($container->reveal());

        $this->assertSame($exceptionHandler, $containerWrapper->exceptionHandler());
    }

    public function testDefaultThrowableHandlerIsReturned()
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->has(ThrowableHandlerInterface::class)->shouldBeCalled(1)->willReturn(false);
        $containerWrapper = new ContainerWrapper($container->reveal());

        $this->assertInstanceOf(ThrowableHandler::class, $containerWrapper->throwableHandler());
    }

    public function testThrowableHandler()
    {
        $throwableHandler = $this->prophesize(ThrowableHandlerInterface::class)->reveal();
        $container = $this->prophesize(ContainerInterface::class);
        $container->has(ThrowableHandlerInterface::class)->shouldBeCalled(1)->willReturn(true);
        $container->get(ThrowableHandlerInterface::class)->shouldBeCalled(1)->willReturn($throwableHandler);
        $containerWrapper = new ContainerWrapper($container->reveal());

        $this->assertSame($throwableHandler, $containerWrapper->throwableHandler());
    }

    public function testDefaultNotFoundHandlerIsReturned()
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->has(NotFoundHandlerInterface::class)->shouldBeCalled(1)->willReturn(false);
        $containerWrapper = new ContainerWrapper($container->reveal());

        $this->assertInstanceOf(NotFoundHandler::class, $containerWrapper->notFoundHandler());
    }

    public function testNotFoundHandler()
    {
        $notFoundHandler = $this->prophesize(NotFoundHandlerInterface::class)->reveal();
        $container = $this->prophesize(ContainerInterface::class);
        $container->has(NotFoundHandlerInterface::class)->shouldBeCalled(1)->willReturn(true);
        $container->get(NotFoundHandlerInterface::class)->shouldBeCalled(1)->willReturn($notFoundHandler);
        $containerWrapper = new ContainerWrapper($container->reveal());

        $this->assertSame($notFoundHandler, $containerWrapper->notFoundHandler());
    }

    public function testDefaultMethodNotAllowedHandlerIsReturned()
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->has(MethodNotAllowedHandlerInterface::class)->shouldBeCalled(1)->willReturn(false);
        $containerWrapper = new ContainerWrapper($container->reveal());

        $this->assertInstanceOf(MethodNotAllowedHandler::class, $containerWrapper->methodNotAllowed());
    }

    public function testMethodNotAllowedHandler()
    {
        $methodNotAllowed = $this->prophesize(MethodNotAllowedHandlerInterface::class)->reveal();
        $container = $this->prophesize(ContainerInterface::class);
        $container->has(MethodNotAllowedHandlerInterface::class)->shouldBeCalled(1)->willReturn(true);
        $container->get(MethodNotAllowedHandlerInterface::class)->shouldBeCalled(1)->willReturn($methodNotAllowed);
        $containerWrapper = new ContainerWrapper($container->reveal());

        $this->assertSame($methodNotAllowed, $containerWrapper->methodNotAllowed());
    }

    public function testDefaultMatchableIsReturned()
    {
        $request = $this->prophesize(ServerRequestInterface::class)->reveal();
        $container = $this->prophesize(ContainerInterface::class);
        $container->has(MatchableInterface::class)->shouldBeCalled(1)->willReturn(false);
        $container->has(ServerRequestInterface::class)->shouldBeCalled(1)->willReturn(true);
        $container->get(ServerRequestInterface::class)->shouldBeCalled(1)->willReturn($request);
        $containerWrapper = new ContainerWrapper($container->reveal());

        $this->assertInstanceOf(RequestMatchable::class, $containerWrapper->matchableRequest());
    }

    public function testMatchable()
    {
        $matchable = $this->prophesize(MatchableInterface::class)->reveal();
        $container = $this->prophesize(ContainerInterface::class);
        $container->has(MatchableInterface::class)->shouldBeCalled(1)->willReturn(true);
        $container->get(MatchableInterface::class)->shouldBeCalled(1)->willReturn($matchable);
        $containerWrapper = new ContainerWrapper($container->reveal());

        $this->assertSame($matchable, $containerWrapper->matchableRequest());
    }

    public function testDefaultStreamIsReturned()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Moon received an invalid stream instance from the container');

        $container = $this->prophesize(ContainerInterface::class);
        $container->has(StreamInterface::class)->shouldBeCalled(1)->willReturn(false);
        $containerWrapper = new ContainerWrapper($container->reveal());

        $containerWrapper->stream();
    }

    public function testStream()
    {
        $stream = $this->prophesize(StreamInterface::class)->reveal();
        $container = $this->prophesize(ContainerInterface::class);
        $container->has(StreamInterface::class)->shouldBeCalled(1)->willReturn(true);
        $container->get(StreamInterface::class)->shouldBeCalled(1)->willReturn($stream);
        $containerWrapper = new ContainerWrapper($container->reveal());

        $this->assertSame($stream, $containerWrapper->stream());
    }

    public function testDefaultStreamLengthIsReturned()
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->has(ContainerWrapper::STREAM_READ_LENGTH)->shouldBeCalled(1)->willReturn(false);
        $containerWrapper = new ContainerWrapper($container->reveal());

        $this->assertEquals(null, $containerWrapper->streamReadLength());
    }

    public function testStreamLength()
    {
        $streamLength = 10;
        $container = $this->prophesize(ContainerInterface::class);
        $container->has(ContainerWrapper::STREAM_READ_LENGTH)->shouldBeCalled(1)->willReturn(true);
        $container->get(ContainerWrapper::STREAM_READ_LENGTH)->shouldBeCalled(1)->willReturn($streamLength);
        $containerWrapper = new ContainerWrapper($container->reveal());

        $this->assertSame($streamLength, $containerWrapper->streamReadLength());
    }
}