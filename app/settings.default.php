<?php

use DI\ContainerBuilder;
use Firebase\JWT\JWT;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

return function (ContainerBuilder $containerBuilder) {
    // Global Settings Object
    $containerBuilder->addDefinitions(
        [
            'settings' => [
                // displayerror is at bottom of index.php
                'db' => [
                    'host' => 'localhost',
                    'database' => 'slim-api-example',
                    'user' => 'root',
                    'pass' => '',
                ],
                JWT::class => [
                    'secret' => 'secretkey',
                    'algorithm' => 'HS256',
                ],


                LoggerInterface::class => [
                    'name' => 'event-log',
                    // The 8 possible levels are categorized into 4 files. Level can't be given as array in the StreamHandler so it has to be declared for each level
                    'enabled_log_levels' => [
                        // DEBUG
                        [
                            // Same file than INFO
                            'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/info.log',
                            'level' => Logger::DEBUG
                        ],
                        // INFO
                        [
                            'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/info.log',
                            'level' => Logger::INFO
                        ],
                        // NOTICE
                        [
                            // Same file than INFO
                            'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/info.log',
                            'level' => Logger::NOTICE
                        ],
                        // WARNING
                        [
                            'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/warning.log',
                            'level' => Logger::WARNING
                        ],
                        // ERROR
                        [
                            // Own file
                            'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/error.log',
                            'level' => Logger::ERROR
                        ],
                        // CRITICAL
                        [
                            'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/critical.log',
                            'level' => Logger::CRITICAL
                        ],
                        // ALERT
                        [
                            // Same file than CRITICAL
                            'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/critical.log',
                            'level' => Logger::ALERT
                        ],
                        // EMERGENCY
                        [
                            // Same file than CRITICAL
                            'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/critical.log',
                            'level' => Logger::EMERGENCY
                        ],

                    ],
                ],
            ],
        ]
    );
};