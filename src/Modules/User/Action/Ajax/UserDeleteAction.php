<?php

namespace App\Modules\User\Action\Ajax;

use App\Core\Application\Responder\JsonResponder;
use App\Modules\User\Service\UserDeleter;
use Odan\Session\SessionInterface;
use Odan\Session\SessionManagerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class UserDeleteAction
{
    public function __construct(
        private JsonResponder $jsonResponder,
        private UserDeleter $userDeleter,
        private SessionManagerInterface $sessionManager,
        private SessionInterface $session,
    ) {
    }

    /**
     * Action used by two different use cases.
     *  - User deletes its own account
     *  - Admin deletes user account.
     *
     * @param ServerRequestInterface $request The request
     * @param ResponseInterface $response The response
     * @param array $args The routing arguments
     *
     * @return ResponseInterface The response
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args,
    ): ResponseInterface {
        $userIdToDelete = (int)$args['user_id'];
        // Delete user
        $deleted = $this->userDeleter->deleteUser($userIdToDelete);

        if ($deleted) {
            // If user deleted his own account, log him out and send redirect location
            if ((int)$this->session->get('user_id') === $userIdToDelete) {
                $this->sessionManager->destroy();
                $this->sessionManager->start();
                $this->sessionManager->regenerateId();
                $this->session->getFlash()->add(
                    'success',
                    'Successfully deleted your account. You are now logged out.'
                );
            } else {
                $this->session->getFlash()->add(
                    'success',
                    __('Successfully deleted user.')
                );
            }

            return $this->jsonResponder->encodeAndAddToResponse($response, ['status' => 'success']);
        }

        $response = $this->jsonResponder->encodeAndAddToResponse(
            $response,
            ['status' => 'warning', 'message' => 'User not deleted.']
        );

        return $response->withAddedHeader('Warning', 'The account was not deleted');
    }
}
