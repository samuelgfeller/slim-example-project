<?php

namespace App\Application\Actions\User\Ajax;

use App\Application\Responder\Responder;
use App\Domain\Authentication\Exception\ForbiddenException;
use App\Domain\User\Service\UserDeleter;
use Fig\Http\Message\StatusCodeInterface;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class UserSubmitDeleteAction
{
    /**
     * The constructor.
     *
     * @param Responder $responder The responder
     * @param UserDeleter $userDeleter
     * @param SessionInterface $session
     */
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
        try {
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
                        'Successfully deleted user.'
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
        } catch (ForbiddenException $fe) {
            // Not throwing HttpForbiddenException as it's a json request and response should be json too
            return $this->responder->respondWithJson(
                $response,
                ['status' => 'error', 'message' => $fe->getMessage()],
                StatusCodeInterface::STATUS_FORBIDDEN
            );
        }
    }
}
