<?php

namespace App\Application\Actions\Authentication\Page;

use App\Application\Responder\Responder;
use App\Domain\Factory\LoggerFactory;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;
use Psr\Log\LoggerInterface;

class PasswordResetPageAction
{
    private LoggerInterface $logger;

    /**
     * The constructor.
     *
     * @param Responder $responder
     * @param SessionInterface $session
     * @param LoggerFactory $loggerFactory
     */
    public function __construct(
        private readonly Responder $responder,
        private readonly SessionInterface $session,
        LoggerFactory $loggerFactory
    ) {
        $this->logger = $loggerFactory->addFileHandler('error.log')->createInstance('user-service');
    }

    /**
     * Check if token is valid and if yes display password form.
     *
     * @param ServerRequest $request
     * @param Response $response
     *
     * @throws \Throwable
     *
     * @return Response
     */
    public function __invoke(ServerRequest $request, Response $response): Response
    {
        $queryParams = $request->getQueryParams();
        $flash = $this->session->getFlash();

        // There may be other query params e.g. redirect
        if (isset($queryParams['id'], $queryParams['token'])) {
            return $this->responder->render($response, 'authentication/reset-password.html.php', [
                'token' => $queryParams['token'],
                'id' => $queryParams['id'],
            ]);
        }

        // Prevent to log passwords
        $this->logger->error('GET request malformed: ' . json_encode($queryParams));

        return $this->responder->render($response, 'authentication/reset-password.html.php', [
            'formErrorMessage' => 'Token not found. Please click on the link you received via email.',
        ]);
    }
}
