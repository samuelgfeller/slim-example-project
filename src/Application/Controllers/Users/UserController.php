<?php

namespace App\Application\Controllers\Users;

use App\Application\Controllers\Controller;
use App\Domain\Auth\AuthService;
use App\Domain\Exceptions\ValidationException;
use App\Domain\User\User;
use App\Domain\User\UserService;
use App\Domain\User\UserValidation;
use App\Domain\Utility\ArrayReader;
use App\Domain\Validation\OutputEscapeService;
use App\Infrastructure\Exceptions\PersistenceRecordNotFoundException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\Handlers\Strategies\RequestHandler;

class UserController extends Controller
{

    protected UserService $userService;
    protected UserValidation $userValidation;
    protected OutputEscapeService $outputEscapeService;
    protected AuthService $authService;


    public function __construct(
        LoggerInterface $logger,
        UserService $userService,
        AuthService $authService,
        UserValidation $userValidation,
        OutputEscapeService $outputEscapeService
    ) {
        parent::__construct($logger);
        $this->userService = $userService;
        $this->authService = $authService;
        $this->userValidation = $userValidation;
        $this->outputEscapeService = $outputEscapeService;
    }

    /**
     * Returns all users
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function list(Request $request, Response $response): ResponseInterface
    {

    }

    public function get(Request $request, Response $response, array $args): Response
    {

    }

    /**
     * Update user info.
     * Name and Email have to be given.
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function update(Request $request, Response $response, array $args): Response
    {

    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $loggedUserId = (int)$this->getUserIdFromToken($request);
        $id = (int)$args['id'];

        $userRole = $this->authService->getUserRole($loggedUserId);


        // Check if it's admin or if it's its own user
        if ($userRole === 'admin' || $id === $loggedUserId) {
            $validationResult = $this->userValidation->validateDeletion($id, $loggedUserId);
            if ($validationResult->fails()) {
                return $this->respondValidationError($validationResult, $response);
            }

            $deleted = $this->userService->deleteUser($id);
            if ($deleted) {
                return $this->respondWithJson($response, ['status' => 'success', 'message' => 'User deleted']);
            }
            return $this->respondWithJson($response, ['status' => 'error', 'message' => 'User not deleted']);
        }
        $this->logger->notice('User ' . $loggedUserId . ' tried to delete other user with id: ' . $id);

        return $this->respondWithJson(
            $response,
            ['status' => 'error', 'message' => 'You can only delete your user or be an admin to delete others'],
            403
        );
    }
}
