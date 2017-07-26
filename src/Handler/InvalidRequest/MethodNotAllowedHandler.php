<?php

declare(strict_types=1);

namespace Moon\Moon\Handler\InvalidRequest;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class MethodNotAllowedHandler implements MethodNotAllowedHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $response->withStatus(StatusCodeInterface::STATUS_METHOD_NOT_ALLOWED);
    }
}