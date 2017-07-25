<?php

declare(strict_types=1);

namespace Moon\Core\Handler\InvalidRequest;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class NotFoundHandler implements InvalidRequestInterface
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $response->withStatus(404);
    }
}