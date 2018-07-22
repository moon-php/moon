<?php

declare(strict_types=1);

namespace Moon\Moon\Handler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface InvalidRequestHandlerInterface
{
    /**
     * Return a response for invalid request.
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface;
}
