<?php

use App\Application\Middleware\CorsMiddleware;
use App\Application\Middleware\CorsMiddlewareExceptionMiddleware;
use App\Application\Middleware\ErrorHandlerMiddleware;
use App\Application\Middleware\JsonBodyParserMiddleware;
use App\Application\Middleware\JwtClaimMiddleware;
use Psr\Log\LoggerInterface;
use Slim\App;

return function (App $app) {
    $container = $app->getContainer();

    $settings = $container->get('settings');
    $logger = $container->get(LoggerInterface::class);

    $app->add(JwtClaimMiddleware::class);
    $app->add(CorsMiddleware::class);
    $app->add(JsonBodyParserMiddleware::class);
    $app->addRoutingMiddleware();



    $app->add(CorsMiddlewareExceptionMiddleware::class);

    $app->add(ErrorHandlerMiddleware::class);
    /*
     * Add Error Handling Middleware
     *
     * @param bool $displayErrorDetails -> Should be set to false in production
     * @param bool $logError s -> Parameter is passed to the default ErrorHandler
     * @param bool $logErrorDetails -> Display error details in error log
     * which can be replaced by a callable of your choice.

     * Note: This middleware should be added last. It will not handle any exceptions/errors
     * for middleware added after it.
    */
//    $errorMiddleware = $app->addErrorMiddleware(true, true, true, $logger);
//    $errorMiddleware->setDefaultErrorHandler($customErrorHandler);
//    $errorHandler = $errorMiddleware->getDefaultErrorHandler();
//    $errorHandler->registerErrorRenderer('text/html', HtmlErrorRenderer::class);
};
