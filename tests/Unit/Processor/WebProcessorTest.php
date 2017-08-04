<?php

declare(strict_types=1);

namespace Moon\Moon\Unit\Processor;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Moon\Moon\Exception\UnprocessableStageException;
use Moon\Moon\Processor\WebProcessor;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class WebProcessorTest extends TestCase
{
    public function testUnProcessableStages()
    {
        $this->expectException(UnprocessableStageException::class);
        $this->expectExceptionMessage('The stage can\'t be handled');
        $processor = new WebProcessor($this->prophesize(ContainerInterface::class)->reveal());
        try {
            $processor->processStages(['I do not exists'], $this->prophesize(ServerRequestInterface::class)->reveal());
        } catch (UnprocessableStageException $e) {
            $this->assertSame('I do not exists', $e->getStage());
            throw $e;
        }
    }

    /**
     * @dataProvider stagesAndContainerDataProvider
     */
    public function testProcessStages(array $stages, ContainerInterface $container, $expectedResult)
    {
        $processor = new WebProcessor($container);
        $result = $processor->processStages($stages, $this->prophesize(ServerRequestInterface::class)->reveal());
        $this->assertSame($expectedResult, $result);
    }


    public function stagesAndContainerDataProvider()
    {
        $response = $this->prophesize(ResponseInterface::class)->reveal();

        $delegate = $this->prophesize(DelegateInterface::class);
        $delegate->process(Argument::type(ServerRequestInterface::class))->willReturn($response);
        $delegate = $delegate->reveal();

        $middleware = $this->prophesize(MiddlewareInterface::class);
        $middleware->process(Argument::type(ServerRequestInterface::class), $delegate)->willReturn($response);
        $middleware = $middleware->reveal();

        $emptyContainer = $this->prophesize(ContainerInterface::class)->reveal();

        $container = $this->prophesize(ContainerInterface::class);
        $container->has('a middleware')->willReturn(true);
        $container->get('a middleware')->willReturn($middleware);
        $container->has('a delegate')->willReturn(true);
        $container->get('a delegate')->willReturn($delegate);
        $container = $container->reveal();

        return [

            [[function () {
                return 1;
            }, function ($payload) {
                return $payload + 10;
            }], $emptyContainer, 11],

            [[function () {
                return 12;
            }], $emptyContainer, 12],

            [[$middleware, $delegate], $emptyContainer, $response],
            [[$delegate], $emptyContainer, $response],
            [['a middleware', 'a delegate'], $container, $response],
            [['a delegate'], $container, $response]
        ];
    }
}