<?php

namespace App\Application\Actions\Auth;

use App\Application\Responder\Responder;
use App\Domain\Auth\AuthService;
use App\Domain\Exceptions\InvalidCredentialsException;
use App\Domain\Exceptions\ValidationException;
use App\Domain\Factory\LoggerFactory;
use App\Domain\User\User;
use App\Domain\Utility\ArrayReader;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;
use Psr\Log\LoggerInterface;

final class LoginSubmitAction
{
    protected AuthService $authService;
    protected LoggerInterface $logger;
    protected Responder $responder;
    protected SessionInterface $session;


    public function __construct(
        Responder $responder,
        LoggerFactory $logger,
        AuthService $authService,
        SessionInterface $session
    ) {
        $this->responder = $responder;
        $this->authService = $authService;
        $this->logger = $logger->addFileHandler('error.log')
            ->createInstance('auth-login');
        $this->session = $session;
    }

    public function __invoke(ServerRequest $request, Response $response): Response
    {
        $userData = $request->getParsedBody();

        $user = new User(new ArrayReader($userData));

        // Clear all flash messages
        $flash = $this->session->getFlash();
        $flash->clear();

        try {
            // Throws InvalidCredentialsException if not allowed
            $userId = $this->authService->GetUserIdIfAllowedToLogin($user);

            // Clear all session data and regenerate session ID
            $this->session->regenerateId();

            // Add user to session
            $this->session->set('user', $userId);
            // Add success message to flash
            $flash->add('success', 'Login successful');

            $this->logger->info('Successful login from user "' . $user->getEmail() . '"');

            return $this->responder->redirectToRouteName($response, 'post-list-all');
        } catch (ValidationException $exception) {
            $flash->add('error', 'Validation error!');

            // Validation error is logged in AppValidation.php
            return $this->responder->redirectForOnValidationError($response, $exception->getValidationResult(), 'login-page');
        } catch (InvalidCredentialsException $e) {

            $flash->add('error', 'Invalid credentials.');

            // Log error
            $this->logger->notice(
                'InvalidCredentialsException thrown with message: "' . $e->getMessage() . '" user "' . $e->getUserEmail(
                ) . '"'
            );

            return $this->responder->redirectToRouteName($response, 'login-page');
        }
    }
}