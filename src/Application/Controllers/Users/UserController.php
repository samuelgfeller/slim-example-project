<?php

namespace App\Controllers\Users;

use App\Application\Controllers\Controller;
use App\Domain\User\UserRepositoryInterface;
use App\Domain\User\UserService;
use App\Domain\User\UserValidation;
use App\Domain\Validation\OutputEscapeService;
use App\Infrastructure\Persistence\Exceptions\PersistenceRecordNotFoundException;
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
    protected $outputEscapeService;


    public function __construct(
        LoggerInterface $logger,
        UserService $userService,
        UserValidation $userValidation,
        OutputEscapeService $outputEscapeService
    ) {
        parent::__construct($logger);
        $this->userService = $userService;
        $this->userValidation = $userValidation;
        $this->outputEscapeService = $outputEscapeService;

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

            $allUsers = $this->outputEscapeService->escapeTwoDimensionalArray($allUsers);

            $response->withHeader('Content-Type', 'application/json');
            return $this->respondWithJson($response, $allUsers);
        }
        $this->logger->notice('User ' . $loggedUserId . ' tried to view all other users');

        return $this->respondWithJson($response,
            ['status' => 'error', 'message' => 'You have to be admin to view all users'], 403);
    }

    public function get(Request $request, Response $response, array $args): Response
    {
        $loggedUserId = (int)$this->getUserIdFromToken($request);

        $id = (int)$args['id'];

        $userRole = $this->userService->getUserRole($loggedUserId);

        // Check if it's admin or if it's its own user
        if ($userRole === 'admin' || $id === $loggedUserId) {
            $user = $this->userService->findUser($id);
            $user = $this->outputEscapeService->escapeOneDimensionalArray($user);
            return $this->respondWithJson($response, $user);
        }
        $this->logger->notice('User ' . $loggedUserId . ' tried to view other user with id: ' . $id);

        return $this->respondWithJson($response,
            ['status' => 'error', 'message' => 'You can only view your user info or be an admin to view others'], 403);
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

                    if ($validationResult->fails()){
            return $this->respondValidationError($validationResult, $response);
        }

            $updated = $this->userService->updateUser($id, $rawData);

            if ($updated) {
                return $this->respondWithJson($response, ['status' => 'success']);
            }
            return $this->respondWithJson($response, ['status' => 'error', 'message' => 'User wasn\'t updated']);
        }
        $this->logger->notice('User ' . $loggedUserId . ' tried to update other user with id: ' . $id);

        return $this->respondWithJson($response,
            ['status' => 'error', 'message' => 'You can only edit your user info or be an admin to view others'], 403);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $loggedUserId = (int)$this->getUserIdFromToken($request);
        $id = (int)$args['id'];

        $userRole = $this->userService->getUserRole($loggedUserId);


        // Check if it's admin or if it's its own user
        if ($userRole === 'admin' || $id === $loggedUserId) {
            $validationResult = $this->userValidation->validateDeletion($id, $loggedUserId);
                    if ($validationResult->fails()){
            return $this->respondValidationError($validationResult, $response);
        }

            $deleted = $this->userService->deleteUser($id);
            if ($deleted) {
                return $this->respondWithJson($response, ['status' => 'success', 'message' => 'User deleted']);
            }
            return $this->respondWithJson($response, ['status' => 'error', 'message' => 'User not deleted']);
        }
        $this->logger->notice('User ' . $loggedUserId . ' tried to delete other user with id: ' . $id);

        return $this->respondWithJson($response,
            ['status' => 'error', 'message' => 'You can only delete your user or be an admin to delete others'], 403);
    }

    /*    public function create(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
        {
            // see register in authcontroller
        }*/


}
