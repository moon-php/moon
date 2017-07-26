<?php

declare(strict_types=1);

namespace Moon\Moon\Handler\Error;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface ExceptionHandlerInterface
{
    /**
     * Return a Response for an application exception
     *
     * @param Exception $exception
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     *
     * @return ResponseInterface
     */
    public function __invoke(Exception $exception, ServerRequestInterface $request, ResponseInterface $response): ResponseInterface;
}