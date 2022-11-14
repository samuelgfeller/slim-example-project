<?php

namespace App\Application\Actions\User;

use App\Application\Responder\Responder;
use App\Domain\Exceptions\ForbiddenException;
use App\Domain\User\Service\UserDeleter;
use Fig\Http\Message\StatusCodeInterface;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class UserDeleteAction
{
    private Responder $responder;


    /**
     * The constructor.
     *
     * @param Responder $responder The responder
     * @param UserDeleter $userDeleter
     * @param SessionInterface $session
     */
    public function __construct(
        Responder $responder,
        private UserDeleter $userDeleter,
        private SessionInterface $session
    ) {
        $this->responder = $responder;
    }

    /**
     * Action used by two different use cases.
     *  - User deletes its own account
     *  - Admin deletes user account
     *
     * @param ServerRequestInterface $request The request
     * @param ResponseInterface $response The response
     *
     * @param array $args The routing arguments
     * @return ResponseInterface The response
     * @throws \JsonException
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        if (($loggedInUserId = $this->session->get('user_id')) !== null) {
            $userIdToDelete = (int)$args['user_id'];

            try {
                // Delete user
                $deleted = $this->userDeleter->deleteUser($userIdToDelete, $loggedInUserId);

                if ($deleted) {
                    $responseBody = ['status' => 'success'];
                    // If user deleted his own account, log him out and send redirect location
                    if ($loggedInUserId === $userIdToDelete) {
                        $this->session->destroy();
                        $this->session->start();
                        $this->session->regenerateId();
                        $this->session->getFlash()->add(
                            'success',
                            'Successfully deleted account. You are now logged out.'
                        );
                        $responseBody['redirectUrl'] = $this->responder->urlFor('home-page');
                    }
                    return $this->responder->respondWithJson($response, $responseBody);
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
                    ['status' => 'error', 'message' => 'You can only delete your user or be an admin to delete others'],
                    StatusCodeInterface::STATUS_FORBIDDEN
                );
            }
        }
        return $response;
    }
}
