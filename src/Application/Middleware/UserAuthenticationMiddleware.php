<?php


namespace App\Application\Middleware;

use App\Application\Responder\Responder;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * User auth verification middleware
 *
 * Class UserAuthMiddleware
 * @package App\Application\Middleware
 */
final class UserAuthenticationMiddleware implements MiddlewareInterface
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
    ): ResponseInterface {
        if ($this->session->get('user_id')) {
            // User is logged in
            return $handler->handle($request);
        }

        $response = $this->responder->createResponse();

        // Inform user that he/she has to login first
        $this->session->getFlash()->add('info', 'Please login to access this page.');

        return $this->responder->redirectToRouteName(
            $response, 'login-page', [], ['redirect' => $request->getUri()->getPath()]
        );
    }
}
