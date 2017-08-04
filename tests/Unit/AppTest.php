<?php

declare(strict_types=1);

namespace Moon\Moon;

use ArrayObject;
use Exception;
use Moon\Moon\Collection\MatchablePipelineCollectionInterface;
use Moon\Moon\Handler\ErrorHandlerInterface;
use Moon\Moon\Handler\InvalidRequestHandlerInterface;
use Moon\Moon\Matchable\MatchableRequestInterface;
use Moon\Moon\Pipeline\MatchablePipelineInterface;
use Moon\Moon\Processor\ProcessorInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Promise\ReturnPromise;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;

class AppTest extends TestCase
{
    public function testAppStackIsProperlyExecutedAndReturnRepsone()
    {
        $this->expectOutputString('Hello World');

        $responseBody = $this->prophesize(StreamInterface::class);
        $responseBody->__toString()->willReturn('Hello World');
        $responseBody->isSeekable()->willReturn(false);
        $responseBody->isReadable()->willReturn(true);
        $responseBody = $responseBody->reveal();

        $response = $this->prophesize(ResponseInterface::class);
        $response->getStatusCode()->willReturn(200);
        $response->getHeaders()->willReturn([]);
        $response->getBody()->willReturn($responseBody);
        $response = $response->reveal();

        $processor = $this->prophesize(ProcessorInterface::class);
        $processor->processStages(['PreStageOne', 'PreStageTwo', 'StageOne', 'StageTwo'], Argument::any(ServerRequestInterface::class))->willReturn($response);
        $processor = $processor->reveal();

        $container = $this->prophesize(ContainerInterface::class);
        $container->has(InvalidRequestHandlerInterface::class)->willReturn(true);
        $container->get(InvalidRequestHandlerInterface::class)->willReturn($this->prophesize(InvalidRequestHandlerInterface::class)->reveal());

        $container->has(ResponseInterface::class)->willReturn(true);
        $container->get(ResponseInterface::class)->willReturn($this->prophesize(ResponseInterface::class));

        $container->has(ServerRequestInterface::class)->willReturn(true);
        $container->get(ServerRequestInterface::class)->willReturn($this->prophesize(ServerRequestInterface::class)->reveal());

        $container->has(ProcessorInterface::class)->willReturn(true);
        $container->get(ProcessorInterface::class)->willReturn($processor);

        $matchable = $this->prophesize(MatchablePipelineInterface::class);
        $matchable->matchBy(Argument::any(MatchableRequestInterface::class))->willReturn(true);
        $matchable->stages()->willReturn(['StageOne', 'StageTwo']);
        $matchable = $matchable->reveal();

        $container->has(Argument::any())->willReturn(false);
        $container = $container->reveal();

        $pipelineCollection = $this->prophesize(MatchablePipelineCollectionInterface::class);
        $pipelineCollection->getIterator()->shouldBeCalled(1)->willReturn(new ArrayObject([$matchable]));
        $pipelineCollection = $pipelineCollection->reveal();

        $app = AppFactory::buildFromContainer($container);
        $app->pipe(['PreStageOne', 'PreStageTwo']);
        $app->run($pipelineCollection);
        $this->assertSame(200, http_response_code());
    }


    public function testAppStackIsProperlyExecutedAndReturnString()
    {
        $this->expectOutputString('Hello World');

        $responseBody = $this->prophesize(StreamInterface::class);
        $responseBody->__toString()->willReturn('Hello World');
        $responseBody->write('Hello World')->willReturn(Argument::type('integer'));
        $responseBody->isSeekable()->willReturn(false);
        $responseBody->isReadable()->willReturn(true);
        $responseBody = $responseBody->reveal();

        $response = $this->prophesize(ResponseInterface::class);
        $response->getStatusCode()->willReturn(200);
        $response->getHeaders()->willReturn([]);
        $response->getBody()->willReturn($responseBody);
        $response->withBody($responseBody)->willReturn($response->reveal());
        $response = $response->reveal();

        $processor = $this->prophesize(ProcessorInterface::class);
        $processor->processStages(['StageOne', 'StageTwo'], Argument::any(ServerRequestInterface::class))->willReturn('Hello World');
        $processor = $processor->reveal();

        $container = $this->prophesize(ContainerInterface::class);
        $container->has(InvalidRequestHandlerInterface::class)->willReturn(true);
        $container->get(InvalidRequestHandlerInterface::class)->willReturn($this->prophesize(InvalidRequestHandlerInterface::class)->reveal());

        $container->has(ResponseInterface::class)->willReturn(true);
        $container->get(ResponseInterface::class)->willReturn($response);

        $container->has(ServerRequestInterface::class)->willReturn(true);
        $container->get(ServerRequestInterface::class)->willReturn($this->prophesize(ServerRequestInterface::class)->reveal());

        $container->has(ProcessorInterface::class)->willReturn(true);
        $container->get(ProcessorInterface::class)->willReturn($processor);

        $matchable = $this->prophesize(MatchablePipelineInterface::class);
        $matchable->matchBy(Argument::any(MatchableRequestInterface::class))->willReturn(true);
        $matchable->stages()->willReturn(['StageOne', 'StageTwo']);
        $matchable = $matchable->reveal();

        $container->has(Argument::any())->willReturn(false);
        $container = $container->reveal();

        $pipelineCollection = $this->prophesize(MatchablePipelineCollectionInterface::class);
        $pipelineCollection->getIterator()->shouldBeCalled(1)->willReturn(new ArrayObject([$matchable]));
        $pipelineCollection = $pipelineCollection->reveal();

        $app = AppFactory::buildFromContainer($container);
        $app->run($pipelineCollection);
        $this->assertSame(200, http_response_code());
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

        $invalidRequestHandler = $this->prophesize(InvalidRequestHandlerInterface::class);
        $invalidRequestHandler->__invoke(Argument::type(ServerRequestInterface::class), Argument::type(ResponseInterface::class))->willReturn($notFoundResponse);
        $invalidRequestHandler = $invalidRequestHandler->reveal();

        $container = $this->prophesize(ContainerInterface::class);
        $container->has(InvalidRequestHandlerInterface::class)->willReturn(true);
        $container->get(InvalidRequestHandlerInterface::class)->willReturn($invalidRequestHandler);

        $response = $this->prophesize(ResponseInterface::class);
        $response->withStatus(404)->willReturn($this->prophesize(ResponseInterface::class)->reveal());
        $response = $response->reveal();
        $container->has(ResponseInterface::class)->willReturn(true);
        $container->get(ResponseInterface::class)->willReturn($response);

        $container->has(ServerRequestInterface::class)->willReturn(true);
        $container->get(ServerRequestInterface::class)->willReturn($this->prophesize(ServerRequestInterface::class)->reveal());

        $container->has(Argument::any())->willReturn(false);
        $container = $container->reveal();

        $pipelineCollection = $this->prophesize(MatchablePipelineCollectionInterface::class);
        $pipelineCollection->getIterator()->shouldBeCalled(1)->willReturn(new ArrayObject([]));
        $pipelineCollection = $pipelineCollection->reveal();

        $app = AppFactory::buildFromContainer($container);
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

        $invalidRequestHandler = $this->prophesize(InvalidRequestHandlerInterface::class);
        $invalidRequestHandler->__invoke(Argument::type(ServerRequestInterface::class), Argument::type(ResponseInterface::class))->willReturn($methodNotAllowedResponse);
        $invalidRequestHandler = $invalidRequestHandler->reveal();

        $container = $this->prophesize(ContainerInterface::class);

        $container->has(InvalidRequestHandlerInterface::class)->willReturn(true);
        $container->get(InvalidRequestHandlerInterface::class)->willReturn($invalidRequestHandler);

        $response = $this->prophesize(ResponseInterface::class);
        $response->withStatus(405)->willReturn($this->prophesize(ResponseInterface::class)->reveal());
        $response = $response->reveal();
        $container->has(ResponseInterface::class)->willReturn(true);
        $container->get(ResponseInterface::class)->willReturn($response);

        $container->has(ServerRequestInterface::class)->willReturn(true);
        $container->get(ServerRequestInterface::class)->willReturn($this->prophesize(ServerRequestInterface::class)->reveal());

        $matchable = $this->prophesize(MatchableRequestInterface::class);
        $matchable->isPatternMatched()->willReturn(true);
        $matchable->request()->willReturn($this->prophesize(ServerRequestInterface::class)->reveal());
        $matchable = $matchable->reveal();

        $container->has(MatchableRequestInterface::class)->willReturn(true);
        $container->get(MatchableRequestInterface::class)->willReturn($matchable);

        $container->has(Argument::any())->willReturn(false);
        $container = $container->reveal();

        $pipelineCollection = $this->prophesize(MatchablePipelineCollectionInterface::class);
        $pipelineCollection->getIterator()->shouldBeCalled(1)->willReturn(new ArrayObject([]));
        $pipelineCollection = $pipelineCollection->reveal();

        $app = AppFactory::buildFromContainer($container);
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

        $errorHandler = $this->prophesize(ErrorHandlerInterface::class);
        $errorHandler->__invoke(Argument::type(Exception::class), Argument::type(ServerRequestInterface::class), Argument::type(ResponseInterface::class))->willReturn($exceptionResponse);
        $errorHandler = $errorHandler->reveal();

        $container = $this->prophesize(ContainerInterface::class);

        $container->has(ErrorHandlerInterface::class)->willReturn(true);
        $container->get(ErrorHandlerInterface::class)->willReturn($errorHandler);

        $container->has(ResponseInterface::class)->willReturn(true);
        $container->get(ResponseInterface::class)->willReturn($this->prophesize(ResponseInterface::class)->reveal());

        $container->has(ServerRequestInterface::class)->willReturn(true);
        $container->get(ServerRequestInterface::class)->willReturn($this->prophesize(ServerRequestInterface::class)->reveal());

        $container->has(Argument::any())->willReturn(false);
        $container = $container->reveal();

        $buggyPipeline = $this->prophesize(MatchablePipelineInterface::class);
        $buggyPipeline->matchBy(Argument::any())->willThrow(Exception::class);
        $buggyPipeline = $buggyPipeline->reveal();

        $pipelineCollection = $this->prophesize(MatchablePipelineCollectionInterface::class);
        $pipelineCollection->getIterator()->shouldBeCalled(1)->willReturn(new ArrayObject([$buggyPipeline]));
        $pipelineCollection = $pipelineCollection->reveal();

        $app = AppFactory::buildFromContainer($container);
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

        $invalidRequestHandler = $this->prophesize(InvalidRequestHandlerInterface::class);
        $invalidRequestHandler->__invoke(Argument::type(ServerRequestInterface::class), Argument::type(ResponseInterface::class))->willReturn($notFoundResponse);
        $invalidRequestHandler = $invalidRequestHandler->reveal();

        $container = $this->prophesize(ContainerInterface::class);

        $container->has(InvalidRequestHandlerInterface::class)->willReturn(true);
        $container->get(InvalidRequestHandlerInterface::class)->willReturn($invalidRequestHandler);

        $response = $this->prophesize(ResponseInterface::class);
        $response->withStatus(404)->willReturn($this->prophesize(ResponseInterface::class)->reveal());
        $response = $response->reveal();
        $container->has(ResponseInterface::class)->willReturn(true);
        $container->get(ResponseInterface::class)->willReturn($response);

        $container->has(ServerRequestInterface::class)->willReturn(true);
        $container->get(ServerRequestInterface::class)->willReturn($this->prophesize(ServerRequestInterface::class)->reveal());

        $container->has(Argument::any())->willReturn(false);
        $container = $container->reveal();

        $pipelineCollection = $this->prophesize(MatchablePipelineCollectionInterface::class);
        $pipelineCollection->getIterator()->shouldBeCalled(1)->willReturn(new ArrayObject([]));
        $pipelineCollection = $pipelineCollection->reveal();

        $app = AppFactory::buildFromContainer($container);
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

        $invalidRequestHandler = $this->prophesize(InvalidRequestHandlerInterface::class);
        $invalidRequestHandler->__invoke(Argument::type(ServerRequestInterface::class), Argument::type(ResponseInterface::class))->willReturn($notFoundResponse);
        $invalidRequestHandler = $invalidRequestHandler->reveal();

        $container = $this->prophesize(ContainerInterface::class);

        $container->has(AppFactory::STREAM_READ_LENGTH)->willReturn(true);
        $container->get(AppFactory::STREAM_READ_LENGTH)->willReturn($chunkLength);

        $container->has(InvalidRequestHandlerInterface::class)->willReturn(true);
        $container->get(InvalidRequestHandlerInterface::class)->willReturn($invalidRequestHandler);

        $response = $this->prophesize(ResponseInterface::class);
        $response->withStatus(404)->willReturn($this->prophesize(ResponseInterface::class)->reveal());
        $response = $response->reveal();
        $container->has(ResponseInterface::class)->willReturn(true);
        $container->get(ResponseInterface::class)->willReturn($response);

        $container->has(ServerRequestInterface::class)->willReturn(true);
        $container->get(ServerRequestInterface::class)->willReturn($this->prophesize(ServerRequestInterface::class)->reveal());

        $container->has(Argument::any())->willReturn(false);
        $container = $container->reveal();

        $pipelineCollection = $this->prophesize(MatchablePipelineCollectionInterface::class);
        $pipelineCollection->getIterator()->shouldBeCalled(1)->willReturn(new ArrayObject([]));
        $pipelineCollection = $pipelineCollection->reveal();

        $app = AppFactory::buildFromContainer($container);
        $app->run($pipelineCollection);
        $this->assertSame(404, http_response_code());
    }

    /**
     * @dataProvider headerDataProvider
     * @runInSeparateProcess
     */
    public function testAllHeaderAreProperlySent($headerToSend, $expectedHeaderSent)
    {
        $responseBody = $this->prophesize(StreamInterface::class);
        $responseBody->isSeekable()->willReturn(false);
        $responseBody->isReadable()->willReturn(false);
        $responseBody = $responseBody->reveal();

        $notFoundResponse = $this->prophesize(ResponseInterface::class);
        $notFoundResponse->getStatusCode()->willReturn(404);
        $notFoundResponse->getHeaders()->willReturn($headerToSend);
        $notFoundResponse->getBody()->willReturn($responseBody);
        $notFoundResponse = $notFoundResponse->reveal();

        $invalidRequestHandler = $this->prophesize(InvalidRequestHandlerInterface::class);
        $invalidRequestHandler->__invoke(Argument::type(ServerRequestInterface::class), Argument::type(ResponseInterface::class))->willReturn($notFoundResponse);
        $invalidRequestHandler = $invalidRequestHandler->reveal();

        $container = $this->prophesize(ContainerInterface::class);
        $container->has(InvalidRequestHandlerInterface::class)->willReturn(true);
        $container->get(InvalidRequestHandlerInterface::class)->willReturn($invalidRequestHandler);

        $response = $this->prophesize(ResponseInterface::class);
        $response->withStatus(404)->willReturn($this->prophesize(ResponseInterface::class)->reveal());
        $response = $response->reveal();
        $container->has(ResponseInterface::class)->willReturn(true);
        $container->get(ResponseInterface::class)->willReturn($response);

        $container->has(ServerRequestInterface::class)->willReturn(true);
        $container->get(ServerRequestInterface::class)->willReturn($this->prophesize(ServerRequestInterface::class)->reveal());

        $container->has(Argument::any())->willReturn(false);
        $container = $container->reveal();

        $pipelineCollection = $this->prophesize(MatchablePipelineCollectionInterface::class);
        $pipelineCollection->getIterator()->shouldBeCalled(1)->willReturn(new ArrayObject([]));
        $pipelineCollection = $pipelineCollection->reveal();

        $app = AppFactory::buildFromContainer($container);
        $app->run($pipelineCollection);
        $this->assertSame(404, http_response_code());
        $this->assertSame($expectedHeaderSent, xdebug_get_headers());
    }

    public function headerDataProvider()
    {
        return [
            [
                ['HeaderNameOne' => ['HeaderValueOne', 'HeaderValueTwo'], 'HeaderValueTwo' => ['HeaderValueTwo']],
                ['HeaderNameOne: HeaderValueOne', 'HeaderNameOne: HeaderValueTwo', 'HeaderValueTwo: HeaderValueTwo']
            ]
        ];
    }
}