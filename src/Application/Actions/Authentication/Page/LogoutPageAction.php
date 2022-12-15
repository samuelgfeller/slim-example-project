<?php

namespace App\Application\Actions\Authentication\Page;

use App\Application\Responder\Responder;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;

final class LogoutPageAction
{
    /**
     * LogoutAction constructor.
     *
     * @param SessionInterface $session
     * @param Responder $responder
     */
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
        $this->session->getFlash()->add('success', 'Logged out successfully.');

        return $this->responder->redirectToRouteName($response, 'login-page');
    }
}
