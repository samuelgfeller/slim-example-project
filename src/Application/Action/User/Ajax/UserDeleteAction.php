<?php

namespace App\Application\Action\User\Ajax;

use App\Application\Responder\Responder;
use App\Domain\User\Service\UserDeleter;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class UserDeleteAction
{
    public function __construct(
        private readonly Responder $responder,
        private readonly UserDeleter $userDeleter,
        private readonly SessionInterface $session
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
     * @throws \JsonException
     *
     * @return ResponseInterface The response
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $userIdToDelete = (int)$args['user_id'];
        // Delete user
        $deleted = $this->userDeleter->deleteUser($userIdToDelete);

        if ($deleted) {
            // If user deleted his own account, log him out and send redirect location
            if ((int)$this->session->get('user_id') === $userIdToDelete) {
                $this->session->destroy();
                $this->session->start();
                $this->session->regenerateId();
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

            return $this->responder->respondWithJson($response, ['status' => 'success']);
        }

        $response = $this->responder->respondWithJson(
            $response,
            // response json body asserted in UserDeleteActionTest
            ['status' => 'warning', 'message' => 'User not deleted.']
        );

        return $response->withAddedHeader('Warning', 'The account was not deleted');
    }
}
