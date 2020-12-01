<?php

use App\Domain\Auth\JwtService;
use App\Domain\Settings;
use Cake\Database\Connection;
use Cake\Database\Driver\Mysql;
use DI\ContainerBuilder;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Log\LoggerInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

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
    // For JWT Auth
    ResponseFactoryInterface::class => function (ContainerInterface $container) {
        return $container->get(App::class)->getResponseFactory();
    },
    // And add this entry
    JwtService::class => function (ContainerInterface $container) {
        $config = $container->get('settings')['jwt'];

        $issuer = (string)$config['issuer'];
        $lifetime = (int)$config['lifetime'];
        $privateKey = (string)$config['private_key'];
        $publicKey = (string)$config['public_key'];

        return new JwtService($issuer, $lifetime, $privateKey, $publicKey);
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

    // Twig templates
    Twig::class => function (ContainerInterface $container) {
        $settings = $container->get('settings');
        $twigSettings = $settings['twig'];

        $options = $twigSettings['options'];
        $options['cache'] = $options['cache_enabled'] ? $options['cache_path'] : false;

        return Twig::create($twigSettings['paths'], $options);
    },

    TwigMiddleware::class => function (ContainerInterface $container) {
        return TwigMiddleware::createFromContainer(
            $container->get(App::class),
            Twig::class
        );
    },
];
