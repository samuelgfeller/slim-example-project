<?php

use App\Controllers\Users\UserController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {
    $app->get('/', function (Request $request, Response $response, array $args) {
        require_once __DIR__ . '/../public/frontend_skeleton/index.html';
        return $response;
    });
    
    $app->group('/users', function (RouteCollectorProxy $group)  {
        $group->get('', UserController::class.':list');
        $group->get('/{id:[0-9]+}', UserController::class.':get');
        $group->put('/{id:[0-9]+}', UserController::class.':update');
        $group->delete('/{id:[0-9]+}', UserController::class.':delete');
        $group->post('', UserController::class.':create');
    });
 
    $app->get('/hello/{name}', function (Request $request, Response $response, array $args) {
        $name = $args['name'];
        $response->getBody()->write("Hello, $name");
        $response->getBody()->write(json_encode($response->getStatusCode()));
        return $response;
    });
};
