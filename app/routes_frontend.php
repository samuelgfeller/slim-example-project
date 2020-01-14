<?php

use App\Controllers\Users\UserController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {
    //    Frontend (temporary)
    $app->get('/', function (Request $request, Response $response, array $args) {

        require_once __DIR__ . '/../public/frontend_skeleton/index.html';
        return $response;
    });

    $app->group('/frontend', function (RouteCollectorProxy $group) {
        $group->get('', function (Request $request, Response $response, array $args) {
            return $response->withHeader('Location', '/frontend/login')->withStatus(302);

            require_once __DIR__ . '/../public/frontend_skeleton/index.html';
            return $response;
        });
        $group->get('/', function (Request $request, Response $response, array $args) {
            return $response->withHeader('Location', '/frontend/login')->withStatus(302);

            require_once __DIR__ . '/../public/frontend_skeleton/index.html';
            return $response;
        });
        $group->get('/user-list', function (Request $request, Response $response, array $args) {
            require_once __DIR__ . '/../public/frontend_skeleton/pages/userlist.html';
            return $response;
        });
        $group->get('/login', function (Request $request, Response $response, array $args) {
            require_once __DIR__ . '/../public/frontend_skeleton/pages/login.html';
            return $response;
        })->setName('login-route');
        $group->get('/login/success', function (Request $request, Response $response, array $args) {
            require_once __DIR__ . '/../public/frontend_skeleton/pages/login.html';
            return $response;
        });
        $group->get('/register', function (Request $request, Response $response, array $args) {
            require_once __DIR__ . '/../public/frontend_skeleton/pages/register.html';
            return $response;
        });
        $group->get('/profile', function (Request $request, Response $response, array $args) {
            require_once __DIR__ . '/../public/frontend_skeleton/pages/profile.html';
            return $response;
        });
        $group->get('/posts', function (Request $request, Response $response, array $args) {
            require_once __DIR__ . '/../public/frontend_skeleton/pages/all_posts.html';
            return $response;
        });
        $group->get('/own-posts', function (Request $request, Response $response, array $args) {
            require_once __DIR__ . '/../public/frontend_skeleton/pages/own_posts.html';
            return $response;
        });


    });
};
