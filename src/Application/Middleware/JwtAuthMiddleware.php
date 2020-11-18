<?php


namespace App\Application\Middleware;

use App\Domain\Auth\JwtService;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * JWT Auth middleware.
 */
final class JwtAuthMiddleware implements MiddlewareInterface
{
    /**
     * @var JwtService
     */
    private JwtService $jwtService;

    /**
     * @var ResponseFactoryInterface
     */
    private ResponseFactoryInterface $responseFactory;

    /**
     * The constructor.
     *
     * @param JwtService $jwtService The JWT auth
     * @param ResponseFactoryInterface $responseFactory The response factory
     */
    public function __construct(
        JwtService $jwtService,
        ResponseFactoryInterface $responseFactory
    ) {
        $this->jwtService = $jwtService;
        $this->responseFactory = $responseFactory;
    }

    /**
     * Invoke middleware.
     *
     * @param ServerRequestInterface $request The request
     * @param RequestHandlerInterface $handler The handler
     *
     * @return ResponseInterface The response
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $token = explode(' ', (string)$request->getHeaderLine('Authorization'))[1] ?? '';

        if (!$token || !$this->jwtService->validateToken($token)) {
            return $this->responseFactory->createResponse()
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(401, 'Unauthorized');
        }

        return $handler->handle($request);
    }
}