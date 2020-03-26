<?php

use App\Application\Middleware\CorsMiddleware;
use App\Application\Middleware\JsonBodyParserMiddleware;
use App\Application\Middleware\SessionMiddleware;
use Psr\Log\LoggerInterface;
use Slim\App;
use Tuupola\Middleware\JwtAuthentication;

return function (App $app) {

    $container = $app->getContainer();

    $settings = $container->get('settings');
    $logger = $container->get(LoggerInterface::class);

    $app->add(CorsMiddleware::class);
    $app->add(SessionMiddleware::class);
    $app->add(JsonBodyParserMiddleware::class);

    $app->add(new JwtAuthentication([
//      'path' => '/api', /* or ["/api", "/admin"] */
        'ignore' => ['/frontend', '/login', '/register', '/hello'],
        'secret' => 'test',//$settings['settings']['jwt']['secret'],
        'algorithm' => ['HS256'],
        'logger' => $logger,
        // HTTPS not mandatory for local development
        'relaxed' => ['localhost', 'dev.slim-api-example','dev.frontend-example'],
        'error' => function ($response, $arguments) {
            $data['status'] = 'error';
            $data['message'] = $arguments['message'];
            return $response->withHeader('Content-Type', 'application/json')->getBody()->write(json_encode($data,
                    JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        }
    ]));
};
