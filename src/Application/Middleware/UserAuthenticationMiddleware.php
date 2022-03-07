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

        $queryParams = [];
        // If header Redirect-to-if-unauthorized is set, add it to the query params of the login route
        if (($routeName = $request->getHeaderLine('Redirect-to-if-unauthorized')) !== '') {
            // Redirect to after login
            $queryParams['redirect'] = $this->responder->urlFor($routeName);
        }

        // If it's a JSON request return 401 with the login url and its possible query params
        if ($request->getHeaderLine('Content-Type') === 'application/json') {
            return $this->responder->respondWithJson(
                $response,
                ['loginUrl' => $this->responder->urlFor('login-page', [], $queryParams)],
                401
            );
        }
        // If no redirect header is set, and it's not a JSON request, redirect to same url as the request after login
        $queryParams = ['redirect' => $request->getUri()->getPath()];
        return $this->responder->redirectToRouteName($response, 'login-page', [], $queryParams);
    }
}
