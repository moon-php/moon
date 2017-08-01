<?php

declare(strict_types=1);

namespace Moon\Moon;

use ArrayObject;
use Exception;
use Moon\Moon\Collection\PipelineCollectionInterface;
use Moon\Moon\Container\ContainerWrapper;
use Moon\Moon\Handler\Error\ExceptionHandlerInterface;
use Moon\Moon\Handler\Error\ThrowableHandlerInterface;
use Moon\Moon\Handler\InvalidRequest\MethodNotAllowedHandlerInterface;
use Moon\Moon\Handler\InvalidRequest\NotFoundHandlerInterface;
use Moon\Moon\Matchable\MatchableInterface;
use Moon\Moon\Pipeline\MatchablePipelineInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Promise\ReturnPromise;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use ReflectionProperty;
use Throwable;
use TypeError;

class AppTest extends TestCase
{
    public function testConstruct()
    {
        $container = $this->prophesize(ContainerInterface::class)->reveal();
        $app = new App($container);
        $reflection = new ReflectionProperty(App::class, 'containerWrapper');
        $reflection->setAccessible(true);
        $containerWrapper = $reflection->getValue($app);
        $reflection = new ReflectionProperty(ContainerWrapper::class, 'container');
        $reflection->setAccessible(true);
        $this->assertSame($container, $reflection->getValue($containerWrapper));
    }

    public function testRunReturnNotFoundResponse()
    {
        $this->expectOutputString('Page not found');

        $responseBody = $this->prophesize(StreamInterface::class);
        $responseBody->__toString()->willReturn('Page not found');
        $responseBody->isSeekable()->willReturn(false);
        $responseBody->isReadable()->willReturn(true);
        $responseBody = $responseBody->reveal();

        $notFoundResponse = $this->prophesize(ResponseInterface::class);
        $notFoundResponse->getStatusCode()->willReturn(404);
        $notFoundResponse->getHeaders()->willReturn([]);
        $notFoundResponse->getBody()->willReturn($responseBody);
        $notFoundResponse = $notFoundResponse->reveal();

        $notFoundHandler = $this->prophesize(NotFoundHandlerInterface::class);
        $notFoundHandler->__invoke(Argument::type(ServerRequestInterface::class), Argument::type(ResponseInterface::class))->willReturn($notFoundResponse);
        $notFoundHandler = $notFoundHandler->reveal();

        $container = $this->prophesize(ContainerInterface::class);
        $container->has(NotFoundHandlerInterface::class)->willReturn(true);
        $container->get(NotFoundHandlerInterface::class)->willReturn($notFoundHandler);

        $container->has(ResponseInterface::class)->willReturn(true);
        $container->get(ResponseInterface::class)->willReturn($this->prophesize(ResponseInterface::class)->reveal());

        $container->has(ServerRequestInterface::class)->willReturn(true);
        $container->get(ServerRequestInterface::class)->willReturn($this->prophesize(ServerRequestInterface::class)->reveal());

        $container->has(Argument::any())->willReturn(false);
        $container = $container->reveal();

        $pipelineCollection = $this->prophesize(PipelineCollectionInterface::class);
        $pipelineCollection->getIterator()->shouldBeCalled(1)->willReturn(new ArrayObject([]));
        $pipelineCollection = $pipelineCollection->reveal();

        $app = new App($container);
        $app->run($pipelineCollection);
        $this->assertSame(404, http_response_code());
    }

    public function testRunReturnMethodNotAllowedResponse()
    {
        $this->expectOutputString('Method not allowed');

        $responseBody = $this->prophesize(StreamInterface::class);
        $responseBody->__toString()->willReturn('Method not allowed');
        $responseBody->isSeekable()->willReturn(false);
        $responseBody->isReadable()->willReturn(true);
        $responseBody = $responseBody->reveal();

        $methodNotAllowedResponse = $this->prophesize(ResponseInterface::class);
        $methodNotAllowedResponse->getStatusCode()->willReturn(405);
        $methodNotAllowedResponse->getHeaders()->willReturn([]);
        $methodNotAllowedResponse->getBody()->willReturn($responseBody);
        $methodNotAllowedResponse = $methodNotAllowedResponse->reveal();

        $methodNotAllowedHandler = $this->prophesize(MethodNotAllowedHandlerInterface::class);
        $methodNotAllowedHandler->__invoke(Argument::type(ServerRequestInterface::class), Argument::type(ResponseInterface::class))->willReturn($methodNotAllowedResponse);
        $methodNotAllowedHandler = $methodNotAllowedHandler->reveal();

        $matchable = $this->prophesize(MatchableInterface::class);
        $matchable->isPatternMatched()->willReturn(true);
        $matchable = $matchable->reveal();

        $container = $this->prophesize(ContainerInterface::class);

        $container->has(MethodNotAllowedHandlerInterface::class)->willReturn(true);
        $container->get(MethodNotAllowedHandlerInterface::class)->willReturn($methodNotAllowedHandler);

        $container->has(ResponseInterface::class)->willReturn(true);
        $container->get(ResponseInterface::class)->willReturn($this->prophesize(ResponseInterface::class)->reveal());

        $container->has(ServerRequestInterface::class)->willReturn(true);
        $container->get(ServerRequestInterface::class)->willReturn($this->prophesize(ServerRequestInterface::class)->reveal());

        $container->has(MatchableInterface::class)->willReturn(true);
        $container->get(MatchableInterface::class)->willReturn($matchable);

        $container->has(Argument::any())->willReturn(false);
        $container = $container->reveal();

        $pipelineCollection = $this->prophesize(PipelineCollectionInterface::class);
        $pipelineCollection->getIterator()->shouldBeCalled(1)->willReturn(new ArrayObject([]));
        $pipelineCollection = $pipelineCollection->reveal();

        $app = new App($container);
        $app->run($pipelineCollection);
        $this->assertSame(405, http_response_code());
    }

    public function testRunReturnExceptionResponse()
    {
        $this->expectOutputString('Exception occurred');

        $responseBody = $this->prophesize(StreamInterface::class);
        $responseBody->__toString()->willReturn('Exception occurred');
        $responseBody->isSeekable()->willReturn(false);
        $responseBody->isReadable()->willReturn(true);
        $responseBody = $responseBody->reveal();

        $exceptionResponse = $this->prophesize(ResponseInterface::class);
        $exceptionResponse->getStatusCode()->willReturn(500);
        $exceptionResponse->getHeaders()->willReturn([]);
        $exceptionResponse->getBody()->willReturn($responseBody);
        $exceptionResponse = $exceptionResponse->reveal();

        $exceptionHandler = $this->prophesize(ExceptionHandlerInterface::class);
        $exceptionHandler->__invoke(Argument::type(Exception::class), Argument::type(ServerRequestInterface::class), Argument::type(ResponseInterface::class))->willReturn($exceptionResponse);
        $exceptionHandler = $exceptionHandler->reveal();

        $container = $this->prophesize(ContainerInterface::class);

        $container->has(ExceptionHandlerInterface::class)->willReturn(true);
        $container->get(ExceptionHandlerInterface::class)->willReturn($exceptionHandler);

        $container->has(ResponseInterface::class)->willReturn(true);
        $container->get(ResponseInterface::class)->willReturn($this->prophesize(ResponseInterface::class)->reveal());

        $container->has(ServerRequestInterface::class)->willReturn(true);
        $container->get(ServerRequestInterface::class)->willReturn($this->prophesize(ServerRequestInterface::class)->reveal());

        $container->has(Argument::any())->willReturn(false);
        $container = $container->reveal();

        $buggyPipeline = $this->prophesize(MatchablePipelineInterface::class);
        $buggyPipeline->matchBy(Argument::any())->willThrow(Exception::class);
        $buggyPipeline = $buggyPipeline->reveal();

        $pipelineCollection = $this->prophesize(PipelineCollectionInterface::class);
        $pipelineCollection->getIterator()->shouldBeCalled(1)->willReturn(new ArrayObject([$buggyPipeline]));
        $pipelineCollection = $pipelineCollection->reveal();

        $app = new App($container);
        $app->run($pipelineCollection);
        $this->assertSame(500, http_response_code());
    }


    public function testRunReturnThrowableResponse()
    {
        $this->expectOutputString('Throwable occurred');

        $responseBody = $this->prophesize(StreamInterface::class);
        $responseBody->__toString()->willReturn('Throwable occurred');
        $responseBody->isSeekable()->willReturn(false);
        $responseBody->isReadable()->willReturn(true);
        $responseBody = $responseBody->reveal();

        $throwableResponse = $this->prophesize(ResponseInterface::class);
        $throwableResponse->getStatusCode()->willReturn(500);
        $throwableResponse->getHeaders()->willReturn([]);
        $throwableResponse->getBody()->willReturn($responseBody);
        $throwableResponse = $throwableResponse->reveal();

        $throwableHandler = $this->prophesize(ThrowableHandlerInterface::class);
        $throwableHandler->__invoke(Argument::type(Throwable::class), Argument::type(ServerRequestInterface::class), Argument::type(ResponseInterface::class))->willReturn($throwableResponse);
        $throwableHandler = $throwableHandler->reveal();

        $container = $this->prophesize(ContainerInterface::class);

        $container->has(ThrowableHandlerInterface::class)->willReturn(true);
        $container->get(ThrowableHandlerInterface::class)->willReturn($throwableHandler);

        $container->has(ResponseInterface::class)->willReturn(true);
        $container->get(ResponseInterface::class)->willReturn($this->prophesize(ResponseInterface::class)->reveal());

        $container->has(ServerRequestInterface::class)->willReturn(true);
        $container->get(ServerRequestInterface::class)->willReturn($this->prophesize(ServerRequestInterface::class)->reveal());

        $container->has(Argument::any())->willReturn(false);
        $container = $container->reveal();

        $buggyPipeline = $this->prophesize(MatchablePipelineInterface::class);
        $buggyPipeline->matchBy(Argument::any())->willThrow(TypeError::class);
        $buggyPipeline = $buggyPipeline->reveal();

        $pipelineCollection = $this->prophesize(PipelineCollectionInterface::class);
        $pipelineCollection->getIterator()->shouldBeCalled(1)->willReturn(new ArrayObject([$buggyPipeline]));
        $pipelineCollection = $pipelineCollection->reveal();

        $app = new App($container);
        $app->run($pipelineCollection);
        $this->assertSame(500, http_response_code());
    }

    public function testRunReturnNothingForNotReadableBody()
    {
        $this->expectOutputString('');

        $responseBody = $this->prophesize(StreamInterface::class);
        $responseBody->__toString()->shouldNotBeCalled()->willReturn("I won't be called");
        $responseBody->isSeekable()->willReturn(true);
        $responseBody->isReadable()->willReturn(false);
        $responseBody->rewind()->shouldBeCalled(1);
        $responseBody = $responseBody->reveal();

        $notFoundResponse = $this->prophesize(ResponseInterface::class);
        $notFoundResponse->getStatusCode()->willReturn(404);
        $notFoundResponse->getHeaders()->willReturn([]);
        $notFoundResponse->getBody()->willReturn($responseBody);
        $notFoundResponse = $notFoundResponse->reveal();

        $notFoundHandler = $this->prophesize(NotFoundHandlerInterface::class);
        $notFoundHandler->__invoke(Argument::type(ServerRequestInterface::class), Argument::type(ResponseInterface::class))->willReturn($notFoundResponse);
        $notFoundHandler = $notFoundHandler->reveal();

        $container = $this->prophesize(ContainerInterface::class);

        $container->has(NotFoundHandlerInterface::class)->willReturn(true);
        $container->get(NotFoundHandlerInterface::class)->willReturn($notFoundHandler);

        $container->has(ResponseInterface::class)->willReturn(true);
        $container->get(ResponseInterface::class)->willReturn($this->prophesize(ResponseInterface::class)->reveal());

        $container->has(ServerRequestInterface::class)->willReturn(true);
        $container->get(ServerRequestInterface::class)->willReturn($this->prophesize(ServerRequestInterface::class)->reveal());

        $container->has(Argument::any())->willReturn(false);
        $container = $container->reveal();

        $pipelineCollection = $this->prophesize(PipelineCollectionInterface::class);
        $pipelineCollection->getIterator()->shouldBeCalled(1)->willReturn(new ArrayObject([]));
        $pipelineCollection = $pipelineCollection->reveal();

        $app = new App($container);
        $app->run($pipelineCollection);
        $this->assertSame(404, http_response_code());
    }

    public function testRunBodyByStreamChunk()
    {
        $this->expectOutputString('abcdefghilmnopqrstuv');
        $chunkLength = 10;
        $responseBody = $this->prophesize(StreamInterface::class);
        $responseBody->eof()->shouldBeCalled(3)->will(new ReturnPromise([false, false, true]));
        $responseBody->read($chunkLength)->shouldBeCalled(2)->will(new ReturnPromise(['abcdefghil', 'mnopqrstuv']));
        $responseBody->isSeekable()->willReturn(false);
        $responseBody->isReadable()->willReturn(true);
        $responseBody = $responseBody->reveal();

        $notFoundResponse = $this->prophesize(ResponseInterface::class);
        $notFoundResponse->getStatusCode()->willReturn(404);
        $notFoundResponse->getHeaders()->willReturn([]);
        $notFoundResponse->getBody()->willReturn($responseBody);
        $notFoundResponse = $notFoundResponse->reveal();

        $notFoundHandler = $this->prophesize(NotFoundHandlerInterface::class);
        $notFoundHandler->__invoke(Argument::type(ServerRequestInterface::class), Argument::type(ResponseInterface::class))->willReturn($notFoundResponse);
        $notFoundHandler = $notFoundHandler->reveal();

        $container = $this->prophesize(ContainerInterface::class);

        $container->has(ContainerWrapper::STREAM_READ_LENGTH)->willReturn(true);
        $container->get(ContainerWrapper::STREAM_READ_LENGTH)->willReturn($chunkLength);

        $container->has(NotFoundHandlerInterface::class)->willReturn(true);
        $container->get(NotFoundHandlerInterface::class)->willReturn($notFoundHandler);

        $container->has(ResponseInterface::class)->willReturn(true);
        $container->get(ResponseInterface::class)->willReturn($this->prophesize(ResponseInterface::class)->reveal());

        $container->has(ServerRequestInterface::class)->willReturn(true);
        $container->get(ServerRequestInterface::class)->willReturn($this->prophesize(ServerRequestInterface::class)->reveal());

        $container->has(Argument::any())->willReturn(false);
        $container = $container->reveal();

        $pipelineCollection = $this->prophesize(PipelineCollectionInterface::class);
        $pipelineCollection->getIterator()->shouldBeCalled(1)->willReturn(new ArrayObject([]));
        $pipelineCollection = $pipelineCollection->reveal();

        $app = new App($container);
        $app->run($pipelineCollection);
        $this->assertSame(404, http_response_code());
    }

    // TODO test allHeaderAreProperlySent
    public function allHeaderAreProperlySent()
    {
        //
    }
}