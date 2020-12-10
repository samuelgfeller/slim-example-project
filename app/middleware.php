<?php

use Odan\Session\Middleware\SessionMiddleware;
use Psr\Log\LoggerInterface;
use Slim\App;
use Slim\Middleware\ErrorMiddleware;

return function (App $app) {
    $container = $app->getContainer();

    $app->add(SessionMiddleware::class);

    $app->addRoutingMiddleware();


    //Error middleware should be added last. It will not handle any exceptions/errors
    $app->add(ErrorMiddleware::class);
};
