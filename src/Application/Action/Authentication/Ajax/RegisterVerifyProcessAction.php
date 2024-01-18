<?php

namespace App\Application\Action\Authentication\Ajax;

use App\Application\Renderer\RedirectHandler;
use App\Domain\Authentication\Exception\InvalidTokenException;
use App\Domain\Authentication\Exception\UserAlreadyVerifiedException;
use App\Domain\Authentication\Service\RegisterTokenVerifier;
use Odan\Session\SessionInterface;
use Odan\Session\SessionManagerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Interfaces\RouteParserInterface;

final readonly class RegisterVerifyProcessAction
{
    public function __construct(
        private LoggerInterface $logger,
        private RedirectHandler $redirectHandler,
        private RouteParserInterface $routeParser,
        private SessionManagerInterface $sessionManager,
        private SessionInterface $session,
        private RegisterTokenVerifier $registerTokenVerifier
    ) {
    }

    public function __invoke(ServerRequest $request, Response $response): Response
    {
        $queryParams = $request->getQueryParams();
        $flash = $this->session->getFlash();
        // There may be other query params e.g. redirect
        if (isset($queryParams['id'], $queryParams['token'])) {
            try {
                $userId = $this->registerTokenVerifier->verifyRegisterTokenAndGetUserId(
                    (int)$queryParams['id'],
                    $queryParams['token']
                );

                $flash->add(
                    'success',
                    sprintf(
                        __('Congratulations!<br>Your account has been %s! <br><b>%s</b>'),
                        __('verified'),
                        __('You are now logged in.'),
                    )
                );
                // Log user in
                // Clear all session data and regenerate session ID
                $this->sessionManager->regenerateId();
                // Add user to session
                $this->session->set('user_id', $userId);

                if (isset($queryParams['redirect'])) {
                    return $this->redirectHandler->redirectToUrl($response, $queryParams['redirect']);
                }

                return $this->redirectHandler->redirectToRouteName($response, 'home-page');
            } catch (InvalidTokenException $ite) {
                $flash->add('error', __('Invalid or expired link. Please log in to receive a new link.'));
                $this->logger->error('Invalid or expired token user_verification id: ' . $queryParams['id']);
                $newQueryParam = isset($queryParams['redirect']) ? ['redirect' => $queryParams['redirect']] : [];

                // Redirect to login page with redirect query param if set
                return $this->redirectHandler->redirectToRouteName($response, 'login-page', [], $newQueryParam);
            } catch (UserAlreadyVerifiedException $uave) {
                // Check if already logged in
                if ($this->session->get('user_id') === null) {
                    // If not logged in, redirect to login page with correct further redirect query param
                    // Flash message asserted in RegisterVerifyActionTest.php
                    $flash->add('info', __('You are already verified. Please log in.'));
                    $newQueryParam = isset($queryParams['redirect']) ? ['redirect' => $queryParams['redirect']] : [];

                    return $this->redirectHandler->redirectToRouteName($response, 'login-page', [], $newQueryParam);
                }
                // Already logged in
                $flash->add(
                    'info',
                    sprintf(
                        __('You are already logged in.<br>Would you like to %slogout%s?'),
                        '<a href="' . $this->routeParser->urlFor('logout') . '">',
                        '</a>'
                    )
                );

                if (isset($queryParams['redirect'])) {
                    return $this->redirectHandler->redirectToUrl($response, $queryParams['redirect']);
                }

                return $this->redirectHandler->redirectToRouteName($response, 'home-page');
            }
        }

        $flash->add('error', __('Token not found. Please click on the link you received via email.'));
        // Prevent to log passwords
        $this->logger->error('GET request malformed: ' . json_encode($queryParams));
        // Caught in error handler which displays error page because if POST request body is empty frontend has error
        // Error message same as in tests/Provider/UserProvider->malformedRequestBodyProvider()
        throw new HttpBadRequestException($request, 'Query params malformed.');
    }
}
