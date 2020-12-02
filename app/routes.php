<?php

use App\Application\Actions\PreflightAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Exception\HttpNotFoundException;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {

    $app->options('/login', PreflightAction::class); // Allow preflight requests
    $app->get('/login', \App\Application\Actions\Auth\LoginAction::class)->setName('auth.login');
    $app->post('/login', \App\Application\Actions\Auth\LoginSubmitAction::class)->setName('auth.login');

    $app->options('/register', PreflightAction::class); // Allow preflight requests
    $app->post('/register', \App\Application\Actions\Auth\RegistrationAction::class)->setName('auth.register');

    $app->group('/users', function (RouteCollectorProxy $group) {
        $group->options('', PreflightAction::class); // Allow preflight requests
        $group->get('', \App\Application\Actions\Users\UserListAction::class);

        $group->options('/{id:[0-9]+}', PreflightAction::class); // Allow preflight requests
        $group->get('/{id:[0-9]+}', \App\Application\Actions\Users\UserViewAction::class);
        $group->put('/{id:[0-9]+}', \App\Application\Actions\Users\UserUpdateAction::class);
        $group->delete('/{id:[0-9]+}', \App\Application\Actions\Users\UserDeleteAction::class);
    });

    $app->group('/posts', function (RouteCollectorProxy $group) {
        $group->options('', PreflightAction::class);  // Allow preflight requests
        $group->get('', \App\Application\Actions\Posts\PostListAction::class);
        $group->post('', \App\Application\Actions\Posts\PostCreateAction::class);

        $group->options('/{id:[0-9]+}', PreflightAction::class); // Allow preflight requests
        $group->get('/{id:[0-9]+}', \App\Application\Actions\Posts\PostViewAction::class);
        $group->put('/{id:[0-9]+}', \App\Application\Actions\Posts\PostUpdateAction::class);
        $group->delete('/{id:[0-9]+}', \App\Application\Actions\Posts\PostDeleteAction::class);
    });

    $app->options('/own-posts', PreflightAction::class); // Allow preflight requests
//    $app->options('/own-posts', function (Request $request, Response $response): Response {
//        return $response;
//    });
    $app->get('/own-posts', \App\Application\Actions\Posts\PostViewOwnAction::class);

    $app->get('/hello/{name}', function (Request $request, Response $response, array $args) {
        $name = $args['name'];
        $response->getBody()->write("Hello, $name");
        $response->getBody()->write(json_encode($response->getStatusCode()));
        throw new HttpInternalServerErrorException('Nooooooooooooooo!');
        return $response;
    });

/*    $app->options('/{routes:.+}', function ($request, $response, $args) {
        return $response;
    });*/


    /**
     * Catch-all route to serve a 404 Not Found page if none of the routes match
     * NOTE: make sure this route is defined last
     */
    $app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function ($request, $response) {
        throw new HttpNotFoundException($request);
    });
};
