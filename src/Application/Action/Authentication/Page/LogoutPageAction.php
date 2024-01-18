<?php

namespace App\Application\Action\Authentication\Page;

use App\Application\Renderer\RedirectHandler;
use Odan\Session\SessionInterface;
use Odan\Session\SessionManagerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;

final readonly class LogoutPageAction
{
    public function __construct(
        private SessionManagerInterface $sessionManager,
        private SessionInterface $session,
        private RedirectHandler $redirectHandler,
    ) {
    }

    public function __invoke(ServerRequest $request, Response $response): Response
    {
        // Logout user
        $this->sessionManager->destroy();
        $this->sessionManager->start();
        $this->sessionManager->regenerateId();
        // Add flash message to inform user of the success
        $this->session->getFlash()->add('success', __('Logged out successfully.'));

        return $this->redirectHandler->redirectToRouteName($response, 'login-page');
    }
}
