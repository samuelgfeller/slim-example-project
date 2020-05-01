<?php
declare(strict_types=1);

use App\Domain\Settings;
use Cake\Database\Connection;
use Cake\Database\Driver\Mysql;
use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        LoggerInterface::class => function (ContainerInterface $c)
        {
            $settings = $c->get('settings');
            $loggerSettings = $settings[LoggerInterface::class];
            $logger = new Logger($loggerSettings['name']);
        
            $processor = new UidProcessor();
            $logger->pushProcessor($processor);

            foreach ($loggerSettings['enabled_log_levels'] as $logStreamSettings){
                $handler = new StreamHandler($logStreamSettings['path'], $logStreamSettings['level'],false);
                $logger->pushHandler($handler);
            }

            return $logger;
        },
        Connection::class => function (ContainerInterface $c) {
            $settings = $c->get('settings')['db'];
            $settings['encoding'] = 'UTF8';
            $driver = new Mysql($settings);
            return new Connection(['driver' => $driver]);
        },
        PDO::class => function (ContainerInterface $c){
            $connection = $c->get(Connection::class);
            $connection->getDriver()->connect();
            return $connection->getDriver()->getConnection();
        },
        Settings::class => function (ContainerInterface $c){
            return new Settings($c->get('settings'));
        }
    ]);
};
