<?php

declare(strict_types=1);

namespace Moon\Core\Handler\Error;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class ExceptionHandler implements ErrorHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(Throwable $exception, ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $response->withStatus(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR);
    }
}