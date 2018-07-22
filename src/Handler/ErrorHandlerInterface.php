<?php

declare(strict_types=1);

namespace Moon\Moon\Handler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

interface ErrorHandlerInterface
{
    /**
     * Return a Response for an application error.
     */
    public function __invoke(Throwable $exception, ServerRequestInterface $request, ResponseInterface $response): ResponseInterface;
}
