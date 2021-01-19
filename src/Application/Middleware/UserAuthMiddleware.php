<?php


namespace App\Application\Middleware;

use App\Application\Responder\Responder;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Routing\RouteContext;

/**
 * User auth verification middleware
 *
 * Class UserAuthMiddleware
 * @package App\Application\Middleware
 */
final class UserAuthMiddleware implements MiddlewareInterface
{
    private SessionInterface $session;

    protected Responder $responder;

    public function __construct(SessionInterface $session, Responder $responder)
    {
        $this->session = $session;
        $this->responder = $responder;
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface{
        if ($this->session->get('user')) {
            // User is logged in
            return $handler->handle($request);
        }

        $response = $handler->handle($request);

        return $this->responder->redirectToRouteName($response, 'login-page');
    }
}
