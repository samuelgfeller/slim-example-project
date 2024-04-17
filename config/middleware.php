<?php

use App\Application\Middleware\ForbiddenExceptionMiddleware;
use App\Application\Middleware\InvalidOperationExceptionMiddleware;
use App\Application\Middleware\NonFatalErrorHandlerMiddleware;
use App\Application\Middleware\PhpViewMiddleware;
use App\Application\Middleware\ValidationExceptionMiddleware;
use Odan\Session\Middleware\SessionStartMiddleware;
use Selective\BasePath\BasePathMiddleware;
use Slim\App;
use Slim\Middleware\ErrorMiddleware;

// Slim middlewares are LIFO (last in, first out) so when responding, the order is backwards
// https://github.com/samuelgfeller/slim-example-project/wiki/Middleware#order-of-execution
return function (App $app) {
    $app->addBodyParsingMiddleware();

    // Add new middlewares here

    // Language middleware
    $app->add(\App\Application\Middleware\LocaleMiddleware::class);

    // Put everything possible before PhpViewMiddleware as if there is an error in a middleware,
    // the error page (and layout as well as everything else) needs this middleware loaded to work.
    $app->add(PhpViewMiddleware::class);

    // Retrieve and store ip address, user agent and user id (has to be BEFORE SessionStartMiddleware as it is using it
    // but after PhpViewMiddleware as it needs the user id)
    $app->add(\App\Application\Middleware\UserNetworkSessionDataMiddleware::class);

    // Has to be before every middleware that needs a started session (LIFO)
    $app->add(SessionStartMiddleware::class);

    // Has to be after PhpViewMiddleware https://www.slimframework.com/docs/v4/cookbook/retrieving-current-route.html
    $app->addRoutingMiddleware();

    // Has to be after Routing (called before on response)
    $app->add(BasePathMiddleware::class);

    // Error middlewares should be added last as the preprocessing (code before the handle() function)
    // will be registered first in a request (LIFO)
    $app->add(ValidationExceptionMiddleware::class);
    $app->add(ForbiddenExceptionMiddleware::class);
    $app->add(InvalidOperationExceptionMiddleware::class);
    // Handle and log notices and warnings (throws ErrorException if displayErrorDetails is true)
    $app->add(NonFatalErrorHandlerMiddleware::class);
    // Set error handler to custom DefaultErrorHandler (defined in container.php)
    $app->add(ErrorMiddleware::class);
};
