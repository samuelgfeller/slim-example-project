<?php

namespace App\Application\Middleware;

use App\Application\Responder\Responder;
use App\Domain\Validation\ValidationException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ValidationExceptionMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly Responder $responder,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (ValidationException $validationException) {
            // Create response (status code and header are added later)
            $response = $this->responder->createResponse();

            $responseData = [
                'status' => 'error',
                'message' => $validationException->getMessage(),
                // The error format is already transformed to the format that the frontend expects in the exception.
                'data' => ['errors' => $validationException->validationErrors],
            ];

            return $this->responder->respondWithJson($response, $responseData, 422);
        }
    }
}
