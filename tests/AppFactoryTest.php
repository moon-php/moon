<?php

declare(strict_types=1);

namespace Moon\Moon\Container;

use Moon\Moon\AppFactory;
use Moon\Moon\Exception\InvalidArgumentException;
use Moon\Moon\Handler\ErrorHandler;
use Moon\Moon\Handler\ErrorHandlerInterface;
use Moon\Moon\Handler\InvalidRequestHandler;
use Moon\Moon\Handler\InvalidRequestHandlerInterface;
use Moon\Moon\Matchable\MatchableRequest;
use Moon\Moon\Matchable\MatchableRequestInterface;
use Moon\Moon\Processor\ProcessorInterface;
use Moon\Moon\Processor\WebProcessor;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionMethod;

class AppFactoryTest extends TestCase
{
    public function testRequest()
    {
        $request = $this->prophesize(ServerRequestInterface::class)->reveal();
        $container = $this->prophesize(ContainerInterface::class);
        $container->has(ServerRequestInterface::class)->shouldBeCalled(1)->willReturn(true);
        $container->get(ServerRequestInterface::class)->shouldBeCalled(1)->willReturn($request);
        $reflection = new ReflectionMethod(AppFactory::class, 'request');
        $reflection->setAccessible(true);

        $this->assertSame($request, $reflection->invoke(null, $container->reveal()));
    }

    public function testRequestNotFound()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Moon received an invalid request instance from the container');

        $container = $this->prophesize(ContainerInterface::class);
        $container->has(ServerRequestInterface::class)->shouldBeCalled(1)->willReturn(false);
        $reflection = new ReflectionMethod(AppFactory::class, 'request');
        $reflection->setAccessible(true);
        $reflection->invoke(null, $container->reveal());
    }

    public function testResponse()
    {
        $response = $this->prophesize(ResponseInterface::class)->reveal();
        $container = $this->prophesize(ContainerInterface::class);
        $container->has(ResponseInterface::class)->shouldBeCalled(1)->willReturn(true);
        $container->get(ResponseInterface::class)->shouldBeCalled(1)->willReturn($response);
        $reflection = new ReflectionMethod(AppFactory::class, 'response');
        $reflection->setAccessible(true);

        $this->assertSame($response, $reflection->invoke(null, $container->reveal()));
    }

    public function testResponseNotFound()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Moon received an invalid response instance from the container');

        $container = $this->prophesize(ContainerInterface::class);
        $container->has(ResponseInterface::class)->shouldBeCalled(1)->willReturn(false);
        $reflection = new ReflectionMethod(AppFactory::class, 'response');
        $reflection->setAccessible(true);
        $reflection->invoke(null, $container->reveal());
    }

    public function testDefaultProcessorIsReturned()
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->has(ProcessorInterface::class)->shouldBeCalled(1)->willReturn(false);
        $reflection = new ReflectionMethod(AppFactory::class, 'processor');
        $reflection->setAccessible(true);

        $this->assertInstanceOf(WebProcessor::class, $reflection->invoke(null, $container->reveal()));
    }

    public function testProcessor()
    {
        $processor = $this->prophesize(ProcessorInterface::class)->reveal();
        $container = $this->prophesize(ContainerInterface::class);
        $container->has(ProcessorInterface::class)->shouldBeCalled(1)->willReturn(true);
        $container->get(ProcessorInterface::class)->shouldBeCalled(1)->willReturn($processor);
        $reflection = new ReflectionMethod(AppFactory::class, 'processor');
        $reflection->setAccessible(true);

        $this->assertSame($processor, $reflection->invoke(null, $container->reveal()));
    }

    public function testDefaultErrorHandlerIsReturned()
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->has(ErrorHandlerInterface::class)->shouldBeCalled(1)->willReturn(false);
        $reflection = new ReflectionMethod(AppFactory::class, 'errorHandler');
        $reflection->setAccessible(true);

        $this->assertInstanceOf(ErrorHandler::class, $reflection->invoke(null, $container->reveal()));

    }

    public function testExceptionHandler()
    {
        $errorHandler = $this->prophesize(ErrorHandlerInterface::class)->reveal();
        $container = $this->prophesize(ContainerInterface::class);
        $container->has(ErrorHandlerInterface::class)->shouldBeCalled(1)->willReturn(true);
        $container->get(ErrorHandlerInterface::class)->shouldBeCalled(1)->willReturn($errorHandler);
        $reflection = new ReflectionMethod(AppFactory::class, 'errorHandler');
        $reflection->setAccessible(true);

        $this->assertSame($errorHandler, $reflection->invoke(null, $container->reveal()));
    }

    public function testDefaultInvalidReuqestHandlerIsReturned()
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->has(InvalidRequestHandlerInterface::class)->shouldBeCalled(1)->willReturn(false);
        $reflection = new ReflectionMethod(AppFactory::class, 'invalidRequestHandler');
        $reflection->setAccessible(true);

        $this->assertInstanceOf(InvalidRequestHandler::class, $reflection->invoke(null, $container->reveal()));
    }

    public function testInvalidReuqestHandler()
    {
        $invalidRequestHandler = $this->prophesize(InvalidRequestHandlerInterface::class)->reveal();
        $container = $this->prophesize(ContainerInterface::class);
        $container->has(InvalidRequestHandlerInterface::class)->shouldBeCalled(1)->willReturn(true);
        $container->get(InvalidRequestHandlerInterface::class)->shouldBeCalled(1)->willReturn($invalidRequestHandler);
        $reflection = new ReflectionMethod(AppFactory::class, 'invalidRequestHandler');
        $reflection->setAccessible(true);

        $this->assertSame($invalidRequestHandler, $reflection->invoke(null, $container->reveal()));
    }

    public function testDefaultMatchableIsReturned()
    {
        $request = $this->prophesize(ServerRequestInterface::class)->reveal();
        $container = $this->prophesize(ContainerInterface::class);
        $container->has(MatchableRequestInterface::class)->shouldBeCalled(1)->willReturn(false);
        $container->get(ServerRequestInterface::class)->shouldBeCalled(1)->willReturn($request);
        $reflection = new ReflectionMethod(AppFactory::class, 'matchableRequest');
        $reflection->setAccessible(true);

        $this->assertInstanceOf(MatchableRequest::class, $reflection->invoke(null, $container->reveal()));

    }

    public function testMatchable()
    {
        $matchable = $this->prophesize(MatchableRequestInterface::class)->reveal();
        $container = $this->prophesize(ContainerInterface::class);
        $container->has(MatchableRequestInterface::class)->shouldBeCalled(1)->willReturn(true);
        $container->get(MatchableRequestInterface::class)->shouldBeCalled(1)->willReturn($matchable);
        $reflection = new ReflectionMethod(AppFactory::class, 'matchableRequest');
        $reflection->setAccessible(true);

        $this->assertSame($matchable, $reflection->invoke(null, $container->reveal()));
    }

    public function testDefaultStreamLengthIsReturned()
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->has(AppFactory::STREAM_READ_LENGTH)->shouldBeCalled(1)->willReturn(false);
        $reflection = new ReflectionMethod(AppFactory::class, 'streamReadLength');
        $reflection->setAccessible(true);

        $this->assertNull($reflection->invoke(null, $container->reveal()));

    }

    public function testStreamLength()
    {
        $streamReadLength = 10;
        $container = $this->prophesize(ContainerInterface::class);
        $container->has(AppFactory::STREAM_READ_LENGTH)->shouldBeCalled(1)->willReturn(true);
        $container->get(AppFactory::STREAM_READ_LENGTH)->shouldBeCalled(1)->willReturn($streamReadLength);
        $reflection = new ReflectionMethod(AppFactory::class, 'streamReadLength');
        $reflection->setAccessible(true);

        $this->assertSame($streamReadLength, $reflection->invoke(null, $container->reveal()));
    }
}