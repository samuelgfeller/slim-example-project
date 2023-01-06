<?php

use App\Application\Middleware\ErrorHandlerMiddleware;
use App\Application\Middleware\PhpViewExtensionMiddleware;
use Odan\Session\Middleware\SessionMiddleware;
use Selective\BasePath\BasePathMiddleware;
use Slim\App;
use Slim\Middleware\ErrorMiddleware;

return function (App $app) {
    $app->addBodyParsingMiddleware();

    // Slim middlewares are LIFO (last in, first out) so when responding, the order is backwards
    // so BasePathMiddleware is invoked before routing and which is before PhpViewExtensionMiddleware
    $app->add(PhpViewExtensionMiddleware::class);
    // Has to be after PhpViewExtensionMiddleware to be called before on request as session is used in php-view extension
    $app->add(SessionMiddleware::class);
    // Cors middleware has to be before routing so that it is performed after routing (LIFO)
    // $app->add(CorsMiddleware::class); // Middleware added in api group in routes.php
    // Has to be after phpViewExtensionMiddleware https://www.slimframework.com/docs/v4/cookbook/retrieving-current-route.html
    // The RoutingMiddleware should be added after our CORS middleware so routing is performed first
    $app->addRoutingMiddleware();
    // Has to be after Routing (called before on response)
    $app->add(BasePathMiddleware::class);

    // Error middleware should be added last. It will not handle any exceptions/errors
    $app->add(ErrorHandlerMiddleware::class);
    $app->add(ErrorMiddleware::class);
};
