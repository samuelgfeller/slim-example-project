<?php
namespace App\Application\Exceptions;

use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Throwable;

final class CorsMiddlewareException extends RuntimeException
{
    /**
     * @var ResponseInterface
     */
    private ResponseInterface $response;

    public function __construct(
        ResponseInterface $response,
        string $message,
        int $code = 500,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);

        $this->response = $response;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
}
