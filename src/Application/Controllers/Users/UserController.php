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

class UserController extends Controller {

    protected $userService;
    protected $userValidation;

    public function __construct(LoggerInterface $logger, UserService $userService, UserValidation $userValidation) {
        parent::__construct($logger);
        $this->userService = $userService;
        $this->userValidation = $userValidation;
    }

    public function get(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        $user = $this->userService->findUser($id);
//        var_dump($this->container->get('logger'));
//        $response->getBody()->write('GET request');

        $this->logger->info('users/' . $id . ' has been called');
//        var_dump($this->logger);
        return $this->respondWithJson($response, $user);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
//        var_dump($request->getParsedBody());
    
        $data = $request->getParsedBody();

        $name = htmlspecialchars($data['name']);
        $email = htmlspecialchars($data['email']);
//        var_dump($data);
        $updated = $this->userService->updateUser($id,$name,$email);
        if ($updated) {
            return $this->respondWithJson($response, ['status' => 'success']);
        }
        return $this->respondWithJson($response, ['status' => 'error']);
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
        if(null !== $data) {
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

            $userData['password'] = password_hash($userData['password1'],PASSWORD_DEFAULT);
            unset($userData['password1'], $userData['password2']);

            $insertId = $this->userService->createUser($userData);

            if (null !== $insertId) {
                return $this->respondWithJson($response, ['status' => 'success']);
            }
            return $this->respondWithJson($response, ['status' => 'error', 'message' => 'User could not be inserted']);
        }
        return $this->respondWithJson($response, ['status' => 'error', 'message' => 'Request body empty']);
    }

    public function list(Request $request, Response $response, array $args) {
        $allUsers = $this->userService->findAllUsers();
        //somehow that doesnt work
//        $this->respondWithData($response, va$allUsers);
        //    $this->respondWithDataPrettyJson($response, $allUsers);

        // This works though
//         $response->getBody()->write(json_encode($allUsers));
//         $response->getBody()->write('omg');
        $response->withHeader('Content-Type', 'application/json');
        return $this->respondWithJson($response, $allUsers);

    }
}
