<?php

namespace App\Application\Middleware;

use App\Application\Responder\Responder;
use App\Domain\User\Enum\UserStatus;
use App\Domain\User\Service\UserFinder;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * User auth verification middleware.
 *
 * Class UserAuthMiddleware
 */
final class UserAuthenticationMiddleware implements MiddlewareInterface
{
    public function __construct(
        protected readonly SessionInterface $session,
        protected readonly Responder $responder,
        protected readonly UserFinder $userFinder,
    ) {
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        // Check if user is logged in
        if (($loggedInUserId = $this->session->get('user_id')) !== null) {
            // Check that the user status is active
            if ($this->userFinder->findUserById($loggedInUserId)->status === UserStatus::Active) {
                return $handler->handle($request);
            }
            // Log user out if not active
            $this->session->destroy();
            $this->session->start();
            $this->session->regenerateId();
        }

        $response = $this->responder->createResponse();

        // Inform user that he/she has to login first
        $this->session->getFlash()->add('info', 'Please login to access this page.');

        $queryParams = [];
        // If header Redirect-to-route-name-if-unauthorized is set, add it to the query params of the login route
        if (($routeName = $request->getHeaderLine('Redirect-to-route-name-if-unauthorized')) !== '') {
            // Redirect to after login
            $queryParams['redirect'] = $this->responder->urlFor($routeName);
        }
        // If header Redirect-to-route-name-if-unauthorized is set, add it to the query params of the login route
        if (($routeName = $request->getHeaderLine('Redirect-to-url-if-unauthorized')) !== '') {
            // Redirect to after login
            $queryParams['redirect'] = $routeName;
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
