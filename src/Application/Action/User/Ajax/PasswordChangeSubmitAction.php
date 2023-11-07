<?php

namespace App\Application\Action\User\Ajax;

use App\Application\Responder\JsonResponder;
use App\Domain\Authentication\Service\PasswordChanger;
use Odan\Session\SessionInterface;
use Odan\Session\SessionManagerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;

/**
 * When user wants to change password being authenticated.
 */
class PasswordChangeSubmitAction
{
    public function __construct(
        private readonly JsonResponder $jsonResponder,
        private readonly SessionManagerInterface $sessionManager,
        private readonly SessionInterface $session,
        private readonly PasswordChanger $passwordChanger
    ) {
    }

    /**
     * Password change submit action.
     *
     * @param ServerRequest $request
     * @param Response $response
     * @param array $args
     *
     * @return Response
     */
    public function __invoke(ServerRequest $request, Response $response, array $args): Response
    {
        $parsedBody = (array)$request->getParsedBody();
        $userId = $args['user_id'];

        $this->passwordChanger->changeUserPassword($parsedBody, $userId);

        // Clear all session data and regenerate session ID if changed user is the one authenticated
        if ((int)$this->session->get('user_id') === $userId) {
            $this->sessionManager->regenerateId();
        }

        return $this->jsonResponder->respondWithJson($response, ['status' => 'success', 'data' => null]);
    }
}
