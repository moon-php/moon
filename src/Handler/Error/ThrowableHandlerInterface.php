<?php

declare(strict_types=1);

namespace Moon\Moon\Handler\Error;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

interface ThrowableHandlerInterface
{
    /**
     * Return a Response for an application error
     *
     * @param Throwable $exception
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     *
     * @return ResponseInterface
     */
    public function __invoke(Throwable $exception, ServerRequestInterface $request, ResponseInterface $response): ResponseInterface;
}