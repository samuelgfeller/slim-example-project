<?php
declare(strict_types=1);

use Cake\Database\Connection;
use Cake\Database\Driver\Mysql;
use DI\Container;
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
            $loggerSettings = $settings['logger'];
            $logger = new Logger($loggerSettings['name']);
        
            $processor = new UidProcessor();
            $logger->pushProcessor($processor);
        
            $handler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);
            $logger->pushHandler($handler);
        
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
        }
    ]);
};
