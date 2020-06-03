<?php

use App\Application\Middleware\CorsMiddleware;
use App\Application\Middleware\JsonBodyParserMiddleware;
use Firebase\JWT\JWT;
use Psr\Log\LoggerInterface;
use Slim\App;
use Tuupola\Middleware\JwtAuthentication;

return function (App $app) {
    $container = $app->getContainer();

    $settings = $container->get('settings');
    $logger = $container->get(LoggerInterface::class);

    // JWT Middleware MUST be before other middleware (especially CORS) because jwt response changes the header
    $app->add(
        new JwtAuthentication(
            [
                //      'path' => '/api', /* or ["/api", "/admin"] */
                'ignore' => ['/frontend', '/login', '/register', '/hello'],
                'secret' => $settings[JWT::class]['secret'],
                'algorithm' => [$settings[JWT::class]['algorithm']],
                'logger' => $logger,
                // HTTPS not mandatory for local development
                'relaxed' => ['localhost', 'dev.slim-api-example', 'dev.frontend-example'],
                'error' => function ($response, $arguments) {
                    $data['status'] = 'error';
                    $data['message'] = $arguments['message'];
                    return $response->getBody()->write(
                        json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT, 512)
                    );
                }
            ]
        )
    );

    $app->add(CorsMiddleware::class);
    $app->add(JsonBodyParserMiddleware::class);
    $app->addRoutingMiddleware();

    /*
     * Add Error Handling Middleware
     *
     * @param bool $displayErrorDetails -> Should be set to false in production
     * @param bool $logErrors -> Parameter is passed to the default ErrorHandler
     * @param bool $logErrorDetails -> Display error details in error log
     * which can be replaced by a callable of your choice.

     * Note: This middleware should be added last. It will not handle any exceptions/errors
     * for middleware added after it.
    */
    $errorMiddleware = $app->addErrorMiddleware(true, true, true, $logger);
//    $errorHandler = $errorMiddleware->getDefaultErrorHandler();
//    $errorHandler->registerErrorRenderer('text/html', HtmlErrorRenderer::class);
};
