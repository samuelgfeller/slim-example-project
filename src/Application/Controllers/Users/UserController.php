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

            $validationResult = $this->userValidation->validateUserUpdate($id,$rawData);

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
        $userId = $args['id'];
        /* https://github.com/D4rkMindz/roast.li/blob/master/src/Controller/UserController.php
        $validationResult = $this->userValidation->validateDeletion($userId, $this->getUserId());
                if ($validationResult->fails()) {
                    $responseData = [
                        'status' => 'error',
                        'validation' => $validationResult->toArray(),
                    ];
                    return $this->respondWithJson($response, $responseData, 422);
                }*/
        $deleted = $this->userService->deleteUser($userId);
        if ($deleted) {
            return $this->respondWithJson($response, ['status' => 'success']);
        }
        return $this->respondWithJson($response, ['status' => 'error']);
    }

    public function create(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $data = $request->getParsedBody();
        if (null !== $data) {
            $userData = [
                'name' => htmlspecialchars($data['name']),
                'email' => htmlspecialchars($data['email']),
                'password1' => $data['password1'],
                'password2' => $data['password2'],
            ];

            $validationResult = $this->userValidation->validateUserRegistration($userData);
            if ($validationResult->fails()) {
                $responseData = [
                    'status' => 'error',
                    'validation' => $validationResult->toArray(),
                ];

                return $this->respondWithJson($response, $responseData, 422);
            }

            $userData['password'] = password_hash($userData['password1'], PASSWORD_DEFAULT);
            unset($userData['password1'], $userData['password2']);

            $insertId = $this->userService->createUser($userData);

            if (null !== $insertId) {
                return $this->respondWithJson($response, ['status' => 'success']);
            }
            return $this->respondWithJson($response, ['status' => 'error', 'message' => 'User could not be inserted']);
        }
        return $this->respondWithJson($response, ['status' => 'error', 'message' => 'Request body empty']);
    }


}
