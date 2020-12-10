<?php

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
use Slim\App;
use Slim\Factory\AppFactory;
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
    // For Responder
    ResponseFactoryInterface::class => function (ContainerInterface $container) {
        return $container->get(App::class)->getResponseFactory();
    },
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
        return new PhpRenderer($rendererSettings['path']);
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
];
