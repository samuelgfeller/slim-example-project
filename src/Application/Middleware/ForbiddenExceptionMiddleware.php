<?php

namespace App\Application\Middleware;

use App\Application\Responder\Responder;
use App\Domain\Authentication\Exception\ForbiddenException;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ForbiddenExceptionMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly Responder $responder,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (ForbiddenException $forbiddenException) {
            // Create response (status code and header are added later)
            $response = $this->responder->createResponse();

            return $this->responder->respondWithJson(
                $response,
                [
                    'status' => 'error',
                    'message' => $forbiddenException->getMessage(),
                ],
                StatusCodeInterface::STATUS_FORBIDDEN
            );
        }
    }
}
