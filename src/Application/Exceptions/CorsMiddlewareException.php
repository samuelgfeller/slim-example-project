<?php

namespace App\Application\Exceptions;

use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Throwable;

final class CorsMiddlewareException extends RuntimeException
{
    public function __construct(
        private readonly ResponseInterface $response,
        string $message,
        int $code = 500,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
}
