<?php

namespace App\Application\Action\Authentication\Page;

use App\Application\Responder\Responder;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;

final class LogoutPageAction
{
    public function __construct(
        private readonly SessionInterface $session,
        private readonly Responder $responder,
    ) {
    }

    public function __invoke(ServerRequest $request, Response $response): Response
    {
        // Logout user
        $this->session->destroy();
        $this->session->start();
        $this->session->regenerateId();
        // Add flash message to inform user of the success
        $this->session->getFlash()->add('success', __('Logged out successfully.'));

        return $this->responder->redirectToRouteName($response, 'login-page');
    }
}
