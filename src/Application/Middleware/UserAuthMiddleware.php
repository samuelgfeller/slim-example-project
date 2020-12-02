<?php


namespace App\Application\Middleware;

use App\Application\Responder\Responder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Routing\RouteContext;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * User auth verification middleware
 *
 * Class UserAuthMiddleware
 * @package App\Application\Middleware
 */
final class UserAuthMiddleware implements MiddlewareInterface
{
    private Session $session;

    protected Responder $responder;

    public function __construct(Session $session, Responder $responder)
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

        return $this->responder->redirect($response, 'login-page');
    }
}
