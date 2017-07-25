<?php

declare(strict_types=1);

namespace Moon\Core\Handler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface ErrorHandlerInterface
{
    public function __invoke($exception, ServerRequestInterface $request, ResponseInterface $response): ResponseInterface;
}