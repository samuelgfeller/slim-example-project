<?php

use App\Controller\AuthController;
use App\Controllers\Posts\PostController;
use App\Controllers\Users\UserController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {
    $app->post('/login', AuthController::class . ':login')->setName('auth.login');
    $app->post('/register', AuthController::class . ':register')->setName('auth.register');

    $app->group('/users', function (RouteCollectorProxy $group) {
        $group->get('', UserController::class . ':list');
        $group->get('/{id:[0-9]+}', UserController::class . ':get');
        $group->put('/{id:[0-9]+}', UserController::class . ':update');
        $group->delete('/{id:[0-9]+}', UserController::class . ':delete');
        $group->post('', UserController::class . ':create');
    });

    $app->group('/posts', function (RouteCollectorProxy $group) {
        $group->get('', PostController::class . ':list');
        $group->get('/{id:[0-9]+}', PostController::class . ':get');
        $group->put('/{id:[0-9]+}', PostController::class . ':update');
        $group->delete('/{id:[0-9]+}', PostController::class . ':delete');
        $group->post('', PostController::class . ':create');
    });
    $app->get('/own-posts', PostController::class . ':getOwnPosts');


    $app->get('/hello/{name}', function (Request $request, Response $response, array $args) {
        $name = $args['name'];
        $response->getBody()->write("Hello, $name");
        $response->getBody()->write(json_encode($response->getStatusCode()));
        throw new HttpInternalServerErrorException('Nooooooooooooooo!');
        return $response;
    });
};
