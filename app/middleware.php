<?php

use App\Application\Middleware\ErrorHandlerMiddleware;
use Odan\Session\Middleware\SessionMiddleware;
use Slim\App;
use Slim\Middleware\ErrorMiddleware;

return function (App $app) {

    $app->add(SessionMiddleware::class);

    $app->addRoutingMiddleware();


    //Error middleware should be added last. It will not handle any exceptions/errors
    $app->add(ErrorHandlerMiddleware::class); //
    $app->add(ErrorMiddleware::class);
};
