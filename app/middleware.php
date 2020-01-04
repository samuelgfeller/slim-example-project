<?php

use App\Application\Middleware\JsonBodyParserMiddleware;
use App\Application\Middleware\SessionMiddleware;
use Psr\Log\LoggerInterface;
use Slim\App;

return function (App $app) {

    $container = $app->getContainer();

    $settings = $container->get('settings');
    $logger = $container->get(LoggerInterface::class);


    $app->add(SessionMiddleware::class);
    $app->add(JsonBodyParserMiddleware::class);

    $app->add(new \Tuupola\Middleware\JwtAuthentication([
//        'path' => '/api', /* or ["/api", "/admin"] */
        'ignore' => ['/frontend', '/login'],
        //"attribute" => "decoded_token_data",
        'secret' => 'test',//$settings['settings']['jwt']['secret'],
        'algorithm' => ['HS256'],
        'logger' => $logger,

        // HTTPS not mandatory for local development
        'relaxed' => ['localhost', 'dev.slim_api_skeleton'],
        'error' => function ($response, $arguments) {
            $data['status'] = 'error';
            $data['message'] = $arguments['message'];
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->getBody()->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        }


        /*"error" => function ($response, $arguments) {
            $data["status"] = "error";
            $data["message"] = $arguments["message"];
            return $response
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        }*/
    ]));
};
