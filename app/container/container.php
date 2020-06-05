<?php

use App\Domain\Settings;
use Cake\Database\Connection;
use Cake\Database\Driver\Mysql;
use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Slim\App;
use Slim\Factory\AppFactory;

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
            $logger->pushHandler($handler);
        }

        return $logger;
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
    }
];
