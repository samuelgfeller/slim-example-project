<?php

namespace App\Application\Middleware;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class CorsMiddleware implements MiddlewareInterface
{
    private ResponseFactoryInterface $responseFactory;

    public function __construct(ResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Handle all "OPTIONS" pre-flight requests with an empty response
        // https://developer.mozilla.org/en-US/docs/Glossary/Preflight_request
        if ($request->getMethod() === 'OPTIONS') {
            // Skips the rest of the middleware stack and returns the response
            $response = $this->responseFactory->createResponse();
        } else {
            // Continue with the middleware stack
            $response = $handler->handle($request);
        }
        // Add response headers in post-processing before the response is sent
        // https://github.com/samuelgfeller/slim-example-project/wiki/Middleware#order-of-execution
        $response = $response
            ->withHeader('Access-Control-Allow-Credentials', 'true')
            ->withHeader('Access-Control-Allow-Origin', '*') // Replace '*' with the domain you want to allow
            ->withHeader('Access-Control-Allow-Headers', '*')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
            ->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->withHeader('Pragma', 'no-cache');

        // Handle warnings and notices, so they won't affect the CORS headers
        if (ob_get_contents()) {
            ob_clean();
        }

        return $response;
    }
}