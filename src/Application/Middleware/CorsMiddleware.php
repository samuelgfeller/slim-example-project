<?php

namespace App\Application\Middleware;

use App\Infrastructure\Settings\Settings;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Adds Access-Control headers to the response.
 * Documentation: https://samuel-gfeller.ch/docs/API-Endpoint.
 */
final class CorsMiddleware implements MiddlewareInterface
{
    private ?string $allowedOrigin;

    public function __construct(private readonly ResponseFactoryInterface $responseFactory, Settings $settings)
    {
        $this->allowedOrigin = $settings->get('api')['allowed_origin'] ?? null;
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
        // https://samuel-gfeller.ch/docs/Slim-Middlewares#order-of-execution
        $response = $response
            ->withHeader('Access-Control-Allow-Credentials', 'true')
            ->withHeader('Access-Control-Allow-Origin', $this->allowedOrigin ?? '')
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
