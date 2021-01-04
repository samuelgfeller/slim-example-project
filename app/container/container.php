<?php

use App\Application\Handler\DefaultErrorHandler;
use App\Application\Middleware\ErrorHandlerMiddleware;
use App\Application\Middleware\PhpViewExtensionMiddleware;
use App\Domain\Settings;
use Cake\Database\Connection;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Odan\Session\Middleware\SessionMiddleware;
use Odan\Session\PhpSession;
use Odan\Session\SessionInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Log\LoggerInterface;
use Selective\BasePath\BasePathMiddleware;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Middleware\ErrorMiddleware;
use Slim\Views\PhpRenderer;

return [
    'settings' => function () {
        return require __DIR__ . '/../settings.php';
    },
    App::class => function (ContainerInterface $container) {
        AppFactory::setContainer($container);
        return AppFactory::create();
    },
    LoggerInterface::class => function (ContainerInterface $container) {
        $settings = $container->get('settings');
        $loggerSettings = $settings[LoggerInterface::class];
        $logger = new Logger($loggerSettings['name']);

        $processor = new UidProcessor();
        $logger->pushProcessor($processor);

        foreach ($loggerSettings['enabled_log_levels'] as $logStreamSettings) {
            $handler = new StreamHandler($logStreamSettings['path'], $logStreamSettings['level'], false);
            // If dev then show error messages with line breaks
            if ($settings['env'] === 'development' && $logStreamSettings['level'] === Logger::ERROR) {
                $handler->setFormatter(new LineFormatter(null, null, true, true));
            }
            $logger->pushHandler($handler);
        }

        return $logger;
    },
    // For Responder and error middleware
    ResponseFactoryInterface::class => function (ContainerInterface $container) {
        return $container->get(App::class)->getResponseFactory();
    },

    // Error middlewares
    ErrorHandlerMiddleware::class => function (ContainerInterface $container) {
        $config = $container->get('settings')['error'];
        $logger = $container->get(LoggerInterface::class);

        return new ErrorHandlerMiddleware(
            (bool)$config['display_error_details'], (bool)$config['log_errors'], $logger,
        );
    },
    ErrorMiddleware::class => function (ContainerInterface $container) {
        $config = $container->get('settings')['error'];
        $app = $container->get(App::class);

        $logger = $container->get(LoggerInterface::class);

        $errorMiddleware = new ErrorMiddleware(
            $app->getCallableResolver(),
            $app->getResponseFactory(),
            (bool)$config['display_error_details'],
            (bool)$config['log_errors'],
            (bool)$config['log_error_details'],
            $logger
        );

        $errorMiddleware->setDefaultErrorHandler($container->get(DefaultErrorHandler::class));

        return $errorMiddleware;
    },

    // Database
    Connection::class => function (ContainerInterface $container) {
        $settings = $container->get('settings')['db'];
        return new Connection($settings);
    },
    PDO::class => function (ContainerInterface $container) {
        $connection = $container->get(Connection::class);
        $connection->getDriver()->connect();
        return $connection->getDriver()->getConnection();
    },
    Settings::class => function (ContainerInterface $container) {
        return new Settings($container->get('settings'));
    },

    // Template renderer
    PhpRenderer::class => function (ContainerInterface $container) {
        $settings = $container->get('settings');
        $rendererSettings = $settings['renderer'];

        // As a second constructor value, global variables can be added
        return new PhpRenderer(
            $rendererSettings['path'], ['title' => 'Slim Example Project'], 'layout/layout.html.php');
    },

    // Sessions
    SessionInterface::class => function (ContainerInterface $container) {
        $settings = $container->get('settings');
        $session = new PhpSession();
        $session->setOptions((array)$settings['session']);

        return $session;
    },

    SessionMiddleware::class => function (ContainerInterface $container) {
        return new SessionMiddleware($container->get(SessionInterface::class));
    },

    BasePathMiddleware::class => function (ContainerInterface $container) {
        return new BasePathMiddleware($container->get(App::class));
    },
    PhpViewExtensionMiddleware::class => function (ContainerInterface $container) {
        return new PhpViewExtensionMiddleware($container->get(App::class), $container->get(PhpRenderer::class));
    },

];
