<?php

use App\Application\Middleware\JsonBodyParserMiddleware;
use App\Application\Middleware\SessionMiddleware;
use Slim\App;
return function (App $app) {

    $settings = $app->getContainer()->get('settings');


    $app->add(SessionMiddleware::class);
    $app->add(JsonBodyParserMiddleware::class);
    $app->add(new \Tuupola\Middleware\JwtAuthentication([
        "path" => "/api", /* or ["/api", "/admin"] */
        //"attribute" => "decoded_token_data",
        "secret" => "test",//$settings['settings']['jwt']['secret'],
        "algorithm" => ["HS256"],
        /*"error" => function ($response, $arguments) {
            $data["status"] = "error";
            $data["message"] = $arguments["message"];
            return $response
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        }*/
    ]));
};
