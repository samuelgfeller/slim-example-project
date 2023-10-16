<?php

use App\Application\Middleware\ErrorHandlerMiddleware;
use App\Application\Middleware\PhpViewExtensionMiddleware;
use Odan\Session\Middleware\SessionStartMiddleware;
use Selective\BasePath\BasePathMiddleware;
use Slim\App;
use Slim\Middleware\ErrorMiddleware;

return function (App $app) {
    $app->addBodyParsingMiddleware();

    // Slim middlewares are LIFO (last in, first out) so when responding, the order is backwards
    // so BasePathMiddleware is invoked before routing and which is before PhpViewExtensionMiddleware

    // Language middleware has to be after PhpViewExtensionMiddleware as it needs the $route parameter
    $app->add(\App\Application\Middleware\LocaleMiddleware::class);

    // Retrieve and store ip address, user agent and user id (has to be BEFORE SessionStartMiddleware as it is using it)
    $app->add(\App\Application\Middleware\UserNetworkSessionDataMiddleware::class);

    // ? Put everything possible before PhpViewExtensionMiddleware as if there is an error in a middleware,
    // the error page (and layout as well as everything else) needs this middleware loaded to work.
    $app->add(PhpViewExtensionMiddleware::class);

    // Has to be after PhpViewExtensionMiddleware to be called before on request as session is used in php-view extension
    // LocaleMiddleware the same, session has to be established. All middlewares that need session must go above this line
    $app->add(SessionStartMiddleware::class);

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
