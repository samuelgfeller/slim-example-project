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
        $user = $this->userService->findUser($id);
//        var_dump($this->container->get('logger'));
        $response->getBody()->write(json_encode($user));
//        $response->getBody()->write('GET request');
        $this->logger->info('users/' . $id . ' has been called');
//        var_dump($this->logger);
        return $response;
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $userId = $args['id'];
        var_dump($request->getBody()->getMetadata());
        $json = $request->getBody()->__toString();
        $data = json_decode($json, true);
        var_dump($json,$data);

        $username = array_value('username', $data);
        $updated = $this->userService->updateUser($id, $name);
        if ($updated) {
            return $this->json($response, ['success' => true]);
        }
        return $this->json($response, ['success' => false]);
        $this->userService->updateUser($args['id']);
//        $request-> get put params
        // https://discourse.slimframework.com/t/slim-v3-put-method-and-retrieve-post-data/849
//        http://www.slimframework.com/docs/v3/objects/request.html
//        https://www.taniarascia.com/how-to-connect-to-an-api-with-javascript/
        $response->getBody()->write('PUT request');
        return $response;
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $response->getBody()->write('DELETE request');
        return $response;
    }

    public function create(Request $request, Response $response, array $args): Response
    {
        $response->getBody()->write('POST request');
        return $response;
    }

    public function list(Request $request, Response $response, array $args) {
        $allUsers = $this->userService->findAllUsers();
        //somehow that doesnt work
//        $this->respondWithData($response, va$allUsers);
        //    $this->respondWithDataPrettyJson($response, $allUsers);

        // This works though
         $response->getBody()->write(json_encode($allUsers));
//         $response->getBody()->write('omg');
        return $response->withHeader('Content-Type', 'application/json');

    }
}
