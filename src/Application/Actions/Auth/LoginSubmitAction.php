<?php

namespace App\Application\Actions\Auth;

use App\Application\Responder\Responder;
use App\Domain\Auth\AuthService;
use App\Domain\Exceptions\InvalidCredentialsException;
use App\Domain\Exceptions\ValidationException;
use App\Domain\User\User;
use App\Domain\User\UserService;
use App\Domain\Utility\ArrayReader;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Session\Session;

final class LoginSubmitAction
{
    protected AuthService $authService;
    protected LoggerInterface $logger;
    protected Responder $responder;
    protected Session $session;


    public function __construct(
        Responder $responder,
        LoggerInterface $logger,
        AuthService $authService,
        Session $session
    ) {
        $this->responder = $responder;
        $this->authService = $authService;
        $this->logger = $logger;
        $this->session = $session;
    }

    public function __invoke(ServerRequest $request, Response $response): Response
    {
        $userData = $request->getParsedBody();

        $user = new User(new ArrayReader($userData));

        // Clear all flash messages
        $flash = $this->session->getFlashBag();
        $flash->clear();

        try {
            // Throws InvalidCredentialsException if not allowed
            $userId = $this->authService->GetUserIdIfAllowedToLogin($user);

            // Clears all session data and regenerates session ID
            $this->session->invalidate();
            $this->session->start();

            $this->session->set('user', $userId);
            $flash->set('success', 'Login successful');

            $this->logger->info('Successful login from user "' . $user->getEmail() . '"');

            return $this->responder->redirect($response, 'post-list-own');
        } catch (ValidationException $exception) {
            $flash->set('error', 'Validation error!');

            // Validation error is logged in AppValidation.php
            return $this->responder->redirectOnValidationError($response, $exception->getValidationResult(), 'login-page');
        } catch (InvalidCredentialsException $e) {

            $flash->set('error', 'Invalid credentials!');

            // Log error
            $this->logger->notice(
                'InvalidCredentialsException thrown with message: "' . $e->getMessage() . '" user "' . $e->getUserEmail(
                ) . '"'
            );

            return $this->responder->redirect($response, 'login-page');
        }
    }
}