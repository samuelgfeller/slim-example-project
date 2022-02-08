<?php

use App\Application\Middleware\PhpViewExtensionMiddleware;
use App\Application\Middleware\ErrorHandlerMiddleware;
use Odan\Session\Middleware\SessionMiddleware;
use Selective\BasePath\BasePathMiddleware;
use Slim\App;
use Slim\Middleware\ErrorMiddleware;

return function (App $app) {
    $app->addBodyParsingMiddleware();
    $app->add(SessionMiddleware::class);

    $app->addRoutingMiddleware();

    // Slim middlewares are LIFO (last in, first out) so when responding, the order is backwards
    // so BasePathMiddleware is invoked before PhpViewExtensionMiddleware and that itself before routing
    $app->add(PhpViewExtensionMiddleware::class);
    $app->add(BasePathMiddleware::class);


    //Error middleware should be added last. It will not handle any exceptions/errors
    $app->add(ErrorHandlerMiddleware::class);
    $app->add(ErrorMiddleware::class);
};
