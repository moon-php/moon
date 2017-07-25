<?php

declare(strict_types=1);

namespace Moon\Core\Handler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ExceptionHandler implements ErrorHandlerInterface
{
    public function __invoke($exception, ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $response;
    }
}