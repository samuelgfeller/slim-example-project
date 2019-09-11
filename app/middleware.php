<?php

use App\Application\Middleware\JsonBodyParserMiddleware;
use App\Application\Middleware\SessionMiddleware;
use Slim\App;
return function (App $app) {
    $app->add(SessionMiddleware::class);
    $app->add(JsonBodyParserMiddleware::class);
};
