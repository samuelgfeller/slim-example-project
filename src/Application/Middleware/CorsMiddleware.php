<?php

namespace App\Application\Middleware;

use App\Domain\Settings;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Routing\RouteContext;
use Throwable;

/**
 * CORS middleware.
 */
final class CorsMiddleware implements MiddlewareInterface
{
    private ?string $allowedOrigin;

    /**
     * @param ResponseFactoryInterface $responseFactory The response factory
     * @param Settings $settings
     */
    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
        Settings $settings,
    ) {
        $this->allowedOrigin = $settings->get('api')['allowed_origin'] ?? null;
    }

    /**
     * Invoke Cors middleware.
     *
     * Source: http://www.slimframework.com/docs/v4/cookbook/enable-cors.html
     * https://odan.github.io/2019/11/24/slim4-cors.html
     *
     * @param ServerRequestInterface $request The request
     * @param RequestHandlerInterface $handler The handler
     *
     * @return ResponseInterface The response
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (isset($this->allowedOrigin)) {
            $routeContext = RouteContext::fromRequest($request);
            $routingResults = $routeContext->getRoutingResults();
            $methods = $routingResults->getAllowedMethods();
            $requestHeaders = $request->getHeaderLine('Access-Control-Request-Headers');

            // In try catch to check if handling the request produces an exception. If yes, the exception should be
            // passed in the response and sent to the client WITH the CORS headers.
            try {
                $response = $handler->handle($request);
            } catch (Throwable $throwable) {
            }

            if (!isset($response)) {
                $response = $this->responseFactory->createResponse(500);
            }

            $response = $response->withHeader('Access-Control-Allow-Origin', $this->allowedOrigin)
                ->withHeader('Access-Control-Allow-Methods', implode(',', $methods))
                ->withHeader('Access-Control-Allow-Headers', $requestHeaders ?: '*');

            // Allow Ajax CORS requests with Authorization header
            // $response = $response->withHeader('Access-Control-Allow-Credentials', 'true');

            if (isset($throwable)) {
                $response = $response->withHeader('Content-Type', 'application/json');

                // Add custom response body here...
                // $response->getBody()->write(
                //     json_encode([
                //         'error' => [
                //             'message' => $throwable->getMessage(),
                //         ],
                //     ], JSON_THROW_ON_ERROR)
                // );

                // Throw exception to pass the response with the CORS headers
                // throw new CorsMiddlewareException($response, $throwable->getMessage(), 500, $throwable);
                throw $throwable;
            }

            return $response;
        }
        // If no allowOrigin url, handle return response
        return $handler->handle($request);
    }
}
