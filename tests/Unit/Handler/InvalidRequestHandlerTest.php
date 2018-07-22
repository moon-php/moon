<?php

declare(strict_types=1);

namespace Moon\Moon\Handler;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class InvalidRequestHandlerTest extends TestCase
{
    public function testWillReturnResponse()
    {
        $request = $this->prophesize(ServerRequestInterface::class)->reveal();
        $response = $this->prophesize(ResponseInterface::class)->reveal();

        $invalidRequestHandler = new InvalidRequestHandler();
        $actualResponse = $invalidRequestHandler->__invoke($request, $response);
        $this->assertSame($response, $actualResponse);
    }
}
