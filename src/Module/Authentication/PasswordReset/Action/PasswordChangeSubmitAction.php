<?php

namespace App\Module\Authentication\PasswordReset\Action;

use App\Application\Responder\JsonResponder;
use App\Module\Authentication\PasswordReset\Service\PasswordChanger;
use Odan\Session\SessionInterface;
use Odan\Session\SessionManagerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * When user wants to change password being authenticated.
 */
final readonly class PasswordChangeSubmitAction
{
    public function __construct(
        private JsonResponder $jsonResponder,
        private SessionManagerInterface $sessionManager,
        private SessionInterface $session,
        private PasswordChanger $passwordChanger,
    ) {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $parsedBody = (array)$request->getParsedBody();
        $userId = $args['user_id'];

        $this->passwordChanger->changeUserPassword($parsedBody, $userId);

        // Clear all session data and regenerate session ID if changed user is the one authenticated
        if ((int)$this->session->get('user_id') === $userId) {
            $this->sessionManager->regenerateId();
        }

        return $this->jsonResponder->encodeAndAddToResponse($response, ['status' => 'success', 'data' => null]);
    }
}
