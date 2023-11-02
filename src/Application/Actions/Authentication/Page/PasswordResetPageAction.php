<?php

namespace App\Application\Actions\Authentication\Page;

use App\Application\Responder\Responder;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;
use Psr\Log\LoggerInterface;

class PasswordResetPageAction
{

    public function __construct(
        private readonly Responder $responder,
        private readonly SessionInterface $session,
        private readonly LoggerInterface $logger,
    ) {
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
        $this->logger->error(
            'GET request malformed: ' . json_encode($queryParams, JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR)
        );
        // If the user clicks on the link and the token's missing, load page with 400 Bad request status
        $response = $response->withStatus(400);

        return $this->responder->render($response, 'authentication/reset-password.html.php', [
            'formErrorMessage' => __('Token not found. Please click on the link you received via email.'),
        ]);
    }
}
