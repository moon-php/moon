<?php

declare(strict_types=1);

namespace Moon\Moon\Processor;

use Moon\Moon\Exception\UnprocessableStageException;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

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

        $requestHandler = $this->prophesize(RequestHandlerInterface::class);
        $requestHandler->handle(Argument::type(ServerRequestInterface::class))->willReturn($response);
        $requestHandler = $requestHandler->reveal();

        $emptyContainer = $this->prophesize(ContainerInterface::class)->reveal();

        $container = $this->prophesize(ContainerInterface::class);
        $container->has('a delegate')->willReturn(true);
        $container->get('a delegate')->willReturn($requestHandler);
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

            [[$requestHandler], $emptyContainer, $response],
            [['a delegate'], $container, $response],
        ];
    }
}
