<?php

declare(strict_types=1);

namespace Moon\Moon\Handler;

use Exception;
use Fig\Http\Message\StatusCodeInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ErrorHandlerTest extends TestCase
{
    public function testWillReturnResponseWithStatusInternalServerError()
    {
        $throwable = $this->prophesize(Exception::class)->reveal();
        $request = $this->prophesize(ServerRequestInterface::class)->reveal();
        $expectedResponse = $this->prophesize(ResponseInterface::class)->reveal();
        $originalResponse = $this->prophesize(ResponseInterface::class);
        $originalResponse->withStatus(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR)->shouldBeCalled(1)->willReturn($expectedResponse);
        $originalResponse = $originalResponse->reveal();

        $throwableHandler = new ErrorHandler();
        $actualResponse = $throwableHandler->__invoke($throwable, $request, $originalResponse);
        $this->assertSame($expectedResponse, $actualResponse);
    }
}
