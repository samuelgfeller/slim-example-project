<?php

use Cake\Database\Driver\Mysql;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

// Init settings var
$settings = [];

// Error handler
$settings['error'] = [
    // Should be set to false in production
    'display_error_details' => false,
    // Should be set to false for unit tests
    'log_errors' => true,
    // Display error details in error log
    'log_error_details' => true,
];

// Secret values are overwritten in env.php
$settings['db'] = [
    'host' => 'localhost',
    'database' => 'slim-api-example',
    'username' => 'root',
    'password' => '',
    'driver' => Mysql::class,
    'encoding' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    // Enable identifier quoting
    'quoteIdentifiers' => true,
    // Disable query logging
    'log' => false,
    // PDO options
    'flags' => [
        // Turn off persistent connections
        PDO::ATTR_PERSISTENT => false,
        // Enable exceptions
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        // Emulate prepared statements
        PDO::ATTR_EMULATE_PREPARES => true,
        // Set default fetch mode to array
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
];

// Secret values are overwritten in env.php
$settings['jwt'] = [
    // The issuer name
    'issuer' => 'www.example.com',

    // Max lifetime in seconds
    'lifetime' => 14400,

    // openssl genrsa -out private.pem 2048
    'private_key' => '-----BEGIN RSA PRIVATE KEY-----
        ...
        -----END RSA PRIVATE KEY-----',

    // openssl rsa -in private.pem -outform PEM -pubout -out public.pem
    'public_key' => '-----BEGIN PUBLIC KEY-----
        ...
        -----END PUBLIC KEY-----',
];

// Template renderer settings
$settings['renderer'] = [
    // Template path
    'path' => __DIR__ . '/../templates',
];

// Session
$settings['session'] = [
    'name' => 'webapp',
    'cache_expire' => 0,
];

$settings[LoggerInterface::class] = [
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
            'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/warning.log',
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
];

return $settings;
