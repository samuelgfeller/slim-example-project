<?php

namespace App\Application\Actions\Authentication;

use App\Application\Responder\Responder;
use App\Domain\Factory\LoggerFactory;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;
use Psr\Log\LoggerInterface;

final class LogoutAction
{
    protected LoggerInterface $logger;
    protected Responder $responder;
    protected SessionInterface $session;


    public function __construct(
        Responder $responder,
        LoggerFactory $logger,
        SessionInterface $session
    ) {
        $this->responder = $responder;
        $this->logger = $logger->addFileHandler('error.log')
            ->createInstance('auth-logout');
        $this->session = $session;
    }

    public function __invoke(ServerRequest $request, Response $response): Response
    {
        // Logout user
        $this->session->destroy();

        return $this->responder->redirectToRouteName(
            $response,
            'login-page',
            ['status' => 'success', 'message' => 'Logged out successfully']
        );
    }
}