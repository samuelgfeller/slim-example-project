<?php

/**
 * Action because it is used by many different modules
 * and Controller.php is an abstract class
 */

namespace App\Application\Actions\Auth;

use App\Application\Responder\Responder;
use App\Domain\Auth\AuthService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Session\Session;

final class LogoutAction
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
        // Logout user
        $this->session->invalidate();

        return $this->responder->redirect(
            $response,
            'login-page',
            ['status' => 'success', 'message' => 'Logged out successfully']
        );
    }
}