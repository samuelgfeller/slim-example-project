<?php

namespace App\Application\Middleware;

use App\Application\Responder\JsonResponder;
use App\Domain\Exception\ValidationException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final readonly class ValidationExceptionMiddleware implements MiddlewareInterface
{
    public function __construct(
        private ResponseFactoryInterface $responseFactory,
        private JsonResponder $jsonResponder,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (ValidationException $validationException) {
            // Create response (status code and header are added later)
            $response = $this->responseFactory->createResponse();

            $responseData = [
                'status' => 'error',
                'message' => $validationException->getMessage(),
                // The error format is already transformed to the format that the frontend expects in the exception.
                'data' => ['errors' => $validationException->validationErrors],
            ];

            return $this->jsonResponder->encodeAndAddToResponse($response, $responseData, 422);
        }
    }
}
