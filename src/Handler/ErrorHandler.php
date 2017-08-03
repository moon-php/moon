<?php

declare(strict_types=1);

namespace Moon\Moon\Handler;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class ErrorHandler implements ErrorHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(Throwable $throwable, ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $response->withStatus(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR);
    }
}