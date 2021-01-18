<?php

namespace App\Application\Actions\Users;

use App\Application\Responder\Responder;
use App\Domain\Auth\AuthService;
use App\Domain\Exceptions\ValidationException;
use App\Domain\Factory\LoggerFactory;
use App\Domain\User\User;
use App\Domain\User\UserService;
use App\Domain\Utility\ArrayReader;
use App\Domain\Validation\OutputEscapeService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final class UserDeleteAction
{
    private Responder $responder;

    protected AuthService $authService;

    protected LoggerInterface $logger;

    protected UserService $userService;

    /**
     * The constructor.
     *
     * @param Responder $responder The responder
     * @param LoggerFactory $logger
     * @param UserService $userService
     * @param AuthService $authService
     */
    public function __construct(
        Responder $responder,
        LoggerFactory $logger,
        UserService $userService,
        AuthService $authService
    ) {
        $this->responder = $responder;
        $this->authService = $authService;
        $this->logger = $logger->addFileHandler('error.log')
            ->createInstance('user-delete');
        $this->userService = $userService;
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
        $loggedUserId = (int)$this->getUserIdFromToken($request);
        $id = (int)$args['id'];

        $userRole = $this->authService->getUserRole($loggedUserId);


        // Check if it's admin or if it's its own user
        if ($userRole === 'admin' || $id === $loggedUserId) {
            // todo [SLE-30] Validate User deletion in Domain instead of Action
            $validationResult = $this->userValidation->validateDeletion($id, $loggedUserId);
            if ($validationResult->fails()) {
                return $this->responder->respondWithJsonOnValidationError($validationResult, $response);
            }

            $deleted = $this->userService->deleteUser($id);
            if ($deleted) {
                return $this->responder->respondWithJson($response, ['status' => 'success', 'message' => 'User deleted']);
            }
            return $this->responder->respondWithJson($response, ['status' => 'error', 'message' => 'User not deleted']);
        }
        $this->logger->notice('User ' . $loggedUserId . ' tried to delete other user with id: ' . $id);

        return $this->responder->respondWithJson(
            $response,
            ['status' => 'error', 'message' => 'You can only delete your user or be an admin to delete others'],
            403
        );
    }
}
