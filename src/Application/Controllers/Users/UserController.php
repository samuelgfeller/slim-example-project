<?php

namespace App\Controllers\Users;

use App\Application\Controllers\Controller;
use App\Domain\User\UserRepositoryInterface;
use App\Domain\User\UserService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;

class UserController extends Controller {

    protected $userService;

    public function __construct(LoggerInterface $logger, UserService $userService) {
        parent::__construct($logger);
        $this->userService = $userService;
    }

    public function get(Request $request, Response $response, array $args) {
        $id = $args['id'];
//        var_dump($this->container->get('logger'));
        $response->getBody()->write('GET request');
        $this->logger->info('locations/' . $id . ' has been called');
//        var_dump($this->logger);
        return $response;
    }

    public function update(Request $request, Response $response, array $args) {
        $response->getBody()->write('PUT request');
        return $response;
    }

    public function delete(Request $request, Response $response, array $args) {
        $response->getBody()->write('DELETE request');
        return $response;
    }

    public function create(Request $request, Response $response, array $args) {
        $response->getBody()->write('POST request');
        return $response;
    }

    public function list(Request $request, Response $response, array $args) {
        $allUsers = $this->userService->findAllUsers();

        //somehow that doesnt work
//        $this->respondWithData($response, $allUsers);
        //    $this->respondWithDataPrettyJson($response, $allUsers);

        // This works though
         $response->getBody()->write(json_encode($allUsers));
        return $response->withHeader('Content-Type', 'application/json');

    }
}
