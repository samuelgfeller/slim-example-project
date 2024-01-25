<?php

namespace App\Application\Action\User\Ajax;

use App\Application\Renderer\JsonEncoder;
use App\Domain\Authentication\Service\PasswordChanger;
use Odan\Session\SessionInterface;
use Odan\Session\SessionManagerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;

/**
 * When user wants to change password being authenticated.
 */
readonly class PasswordChangeSubmitAction
{
    public function __construct(
        private JsonEncoder $jsonEncoder,
        private SessionManagerInterface $sessionManager,
        private SessionInterface $session,
        private PasswordChanger $passwordChanger
    ) {
    }

    public function __invoke(ServerRequest $request, Response $response, array $args): Response
    {
        $parsedBody = (array)$request->getParsedBody();
        $userId = $args['user_id'];

        $this->passwordChanger->changeUserPassword($parsedBody, $userId);

        // Clear all session data and regenerate session ID if changed user is the one authenticated
        if ((int)$this->session->get('user_id') === $userId) {
            $this->sessionManager->regenerateId();
        }

        return $this->jsonEncoder->encodeAndAddToResponse($response, ['status' => 'success', 'data' => null]);
    }
}
