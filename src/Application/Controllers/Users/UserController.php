<?php

namespace App\Controllers\Users;

use App\Application\Controllers\Controller;
use App\Domain\User\UserRepositoryInterface;
use App\Domain\User\UserService;
use App\Domain\User\UserValidation;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\Handlers\Strategies\RequestHandler;

class UserController extends Controller
{

    protected $userService;
    protected $userValidation;

    public function __construct(LoggerInterface $logger, UserService $userService, UserValidation $userValidation)
    {
        parent::__construct($logger);
        $this->userService = $userService;
        $this->userValidation = $userValidation;
    }

    /**
     * Returns all users
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     * @throws \App\Infrastructure\Persistence\Exceptions\PersistenceRecordNotFoundException
     */
    public function list(Request $request, Response $response, array $args)
    {
        $loggedUserId = (int)$this->getUserIdFromToken($request);

        $userRole = $this->userService->getUserRole($loggedUserId);

        if ($userRole === 'admin') {
            $allUsers = $this->userService->findAllUsers();

            $response->withHeader('Content-Type', 'application/json');
            return $this->respondWithJson($response, $allUsers);
        }
        return $this->respondWithJson($response,
            ['status' => 'error', 'message' => 'You have to be admin to view all users'], 401);
    }

    public function get(Request $request, Response $response, array $args): Response
    {
        $loggedUserId = (int)$this->getUserIdFromToken($request);

        $id = (int)$args['id'];

        $userRole = $this->userService->getUserRole($loggedUserId);

        // Check if it's admin or if it's its own user
        if ($userRole === 'admin' || $id === $loggedUserId) {
            $user = $this->userService->findUser($id);
            return $this->respondWithJson($response, $user);
        }

        return $this->respondWithJson($response,
            ['status' => 'error', 'message' => 'You can only view your user info or be an admin to view others'], 401);
    }

    /**
     * Update user info.
     * Name and Email have to be given.
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     * @throws \App\Infrastructure\Persistence\Exceptions\PersistenceRecordNotFoundException
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        $loggedUserId = (int)$this->getUserIdFromToken($request);

        $id = (int)$args['id'];

        $userRole = $this->userService->getUserRole($loggedUserId);

        // Check if it's admin or if it's its own user
        if ($userRole === 'admin' || $id === $loggedUserId) {
            $rawData = $request->getParsedBody();

            $validationResult = $this->userValidation->validateUserUpdate($id, $rawData);

            if ($validationResult->fails()) {
                $responseData = [
                    'status' => 'error',
                    'message' => 'Validation error',
                    'validation' => $validationResult->toArray(),
                ];

                return $this->respondWithJson($response, $responseData, 422);
            }

            $updated = $this->userService->updateUser($id, $rawData);

            if ($updated) {
                return $this->respondWithJson($response, ['status' => 'success']);
            }
            return $this->respondWithJson($response, ['status' => 'error']);
        }
        return $this->respondWithJson($response,
            ['status' => 'error', 'message' => 'You can only edit your user info or be an admin to view others'], 401);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $loggedUserId = (int)$this->getUserIdFromToken($request);
        $id = (int)$args['id'];
        $userRole = $this->userService->getUserRole($loggedUserId);

        // Check if it's admin or if it's its own user
        if ($userRole === 'admin' || $id === $loggedUserId) {
            $validationResult = $this->userValidation->validateDeletion($id, $loggedUserId);
            if ($validationResult->fails()) {
                $responseData = [
                    'status' => 'error',
                    'validation' => $validationResult->toArray(),
                ];
                return $this->respondWithJson($response, $responseData, 422);
            }
            $deleted = $this->userService->deleteUser($id);
            if ($deleted) {
                return $this->respondWithJson($response, ['status' => 'success', 'message' => 'User deleted']);
            }
            return $this->respondWithJson($response, ['status' => 'error', 'message' => 'User not deleted']);
        }
        return $this->respondWithJson($response,
            ['status' => 'error', 'message' => 'You can only delete your user or be an admin to delete others'], 401);
    }

/*    public function create(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        // see register in authcontroller
    }*/


}
