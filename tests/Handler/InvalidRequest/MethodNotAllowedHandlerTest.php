<?php

declare(strict_types=1);

namespace Moon\Moon\Handler\Error;

use Fig\Http\Message\StatusCodeInterface;
use Moon\Moon\Handler\InvalidRequest\MethodNotAllowedHandler;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class MethodNotAllowedHandlerTest extends TestCase
{
    public function testWillReturnResponseWithStatusNotFound()
    {
        $request = $this->prophesize(ServerRequestInterface::class)->reveal();
        $expectedResponse = $this->prophesize(ResponseInterface::class)->reveal();
        $originalResponse = $this->prophesize(ResponseInterface::class);
        $originalResponse->withStatus(StatusCodeInterface::STATUS_METHOD_NOT_ALLOWED)->shouldBeCalled(1)->willReturn($expectedResponse);
        $originalResponse = $originalResponse->reveal();

        $methodNotAllowed = new MethodNotAllowedHandler();
        $actualResponse = $methodNotAllowed->__invoke($request, $originalResponse);
        $this->assertSame($expectedResponse, $actualResponse);
    }
}