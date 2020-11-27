<?php

namespace App\Application\Middleware;

use App\Domain\Auth\JwtService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * JWT Claim middleware.
 */
final class JwtClaimMiddleware implements MiddlewareInterface
{
    /**
     * @var JwtService
     */
    private JwtService $jwtService;

    /**
     * The constructor.
     *
     * @param JwtService $jwtService The JWT auth
     */
    public function __construct(JwtService $jwtService)
    {
        $this->jwtService = $jwtService;
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
        $authorization = explode(' ', (string)$request->getHeaderLine('Authorization'));
        $type = $authorization[0] ?? '';
        $credentials = $authorization[1] ?? '';

        if ($type === 'Bearer' && $this->jwtService->validateToken($credentials)) {
            // Append valid token
            $parsedToken = $this->jwtService->createParsedToken($credentials);
            $request = $request->withAttribute('token', $parsedToken);

            // Append the user id as request attribute
            $request = $request->withAttribute('uid', $parsedToken->getClaim('uid'));

            // Add more claim values as attribute...
            //$request = $request->withAttribute('locale', $parsedToken->getClaim('locale'));
        }

        return $handler->handle($request);
    }
}