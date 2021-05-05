<?php

namespace App\Application\Actions\Auth;

use App\Application\Responder\Responder;
use App\Domain\Auth\AuthService;
use App\Domain\Auth\Exception\InvalidTokenException;
use App\Domain\Auth\Exception\UserAlreadyVerifiedException;
use App\Domain\Factory\LoggerFactory;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpForbiddenException;

final class RegisterVerifyAction
{
    protected LoggerInterface $logger;

    public function __construct(
        LoggerFactory $logger,
        protected Responder $responder,
        protected AuthService $authService,
        private SessionInterface $session
    ) {
        $this->logger = $logger->addFileHandler('error.log')->createInstance('auth-verify-register');
    }

    public function __invoke(ServerRequest $request, Response $response): Response
    {
        $queryParams = $request->getQueryParams();
        $flash = $this->session->getFlash();
        // There may be other query params e.g. redirect
        if (isset($queryParams['id'], $queryParams['token'])) {
            try {
                $this->authService->verifyUser((int)$queryParams['id'], $queryParams['token']);

                $flash->add(
                    'success',
                    'Congratulations! <br> You\'re account has been  verified! <br>' . '<b>You are now logged in.</b>'
                );
                // Log user in
                $userId = $this->authService->getUserIdFromVerification($queryParams['id']);
                // Clear all session data and regenerate session ID
                $this->session->regenerateId();
                // Add user to session
                $this->session->set('user_id', $userId);

                if (isset($queryParams['redirect'])) {
                    $flash->add('info', 'You have been redirected to the site you previously tried to access.');
                    return $this->responder->redirectToUrl($response, $queryParams['redirect']);
                }
                return $this->responder->redirectToRouteName($response, 'home');
            } catch (InvalidTokenException $ite) {
                $flash->add('error', '<b>Invalid or expired link. <br>Please register again.</b>');
                $this->logger->error('Invalid or expired token user_verification' . $queryParams['id']);
                $newQueryParam = isset($queryParams['redirect']) ? ['redirect' => $queryParams['redirect']] : [];
                // Redirect to register page with redirect query param if set
                return $this->responder->redirectToRouteName($response, 'register-page', [], $newQueryParam);
            } catch (UserAlreadyVerifiedException $uave) {
                $flash->add('info', 'You are already verified and should be able to log in.');
                $this->logger->info(
                    'Already verified user tried to verify again. user_verification id: ' . $queryParams['id']
                );
                if (isset($queryParams['redirect'])) {
                    $flash->add('info', 'You have been redirected to the site you previously tried to access.');
                    return $this->responder->redirectToUrl($response, $queryParams['redirect']);
                }
                return $this->responder->redirectToRouteName($response, 'home');
            }
        }

        $flash->add('error', 'Please click on the link you received via email.');
        // Prevent to log passwords
        $this->logger->error('GET request malformed: ' . json_encode($queryParams));
        // Caught in error handler which displays error page because if POST request body is empty frontend has error
        // Error message same as in tests/Provider/UserProvider->malformedRequestBodyProvider()
        throw new HttpBadRequestException($request, 'Query params malformed.');
    }
}