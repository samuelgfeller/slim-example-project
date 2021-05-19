<?php

namespace App\Application\Actions\Users;

use App\Application\Responder\Responder;
use App\Domain\Auth\AuthService;
use App\Domain\Factory\LoggerFactory;
use App\Domain\User\UserService;
use App\Domain\Validation\OutputEscapeService;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

/**
 * Action.
 */
final class UserViewProfileAction
{
    protected LoggerInterface $logger;

    /**
     * The constructor.
     *
     * @param Responder $responder The responder
     * @param LoggerFactory $logger
     * @param UserService $userService
     * @param SessionInterface $session
     */
    public function __construct(
        private Responder $responder,
        LoggerFactory $logger,
        private UserService $userService,
        private SessionInterface $session
    ) {
        $this->logger = $logger->addFileHandler('error.log')
            ->createInstance('auth-view');
    }

    /**
     * Action.
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
        // getUserIdFromToken not transferred to action since it will be session based


        if (($userId = $this->session->get('user_id')) !== null){
            $user = $this->userService->findUserById($userId);
        }

        $userRole = $this->authService->getUserRoleById($loggedUserId);

        // Check if it's admin or if it's its own user
        if ($userRole === 'admin' || $id === $loggedUserId) {
            $user = $this->userService->findUserById($id);
            $user = $this->outputEscapeService->escapeOneDimensionalArray($user);
            return $this->responder->respondWithJson($response, $user);
        }
        $this->logger->notice('User ' . $loggedUserId . ' tried to view other user with id: ' . $id);

        return $this->responder->respondWithJson(
            $response,
            ['status' => 'error', 'message' => 'You can only view your user info or be an admin to view others'],
            403
        );
    }
}
