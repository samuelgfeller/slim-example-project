<?php
/**
 * Default configuration values
 *
 * This file should contain all keys even secret ones to serve as template
 *
 * This is the first file loaded in settings.php and can as such safely define arrays
 * without the risk of overwriting something.
 * Permitted to do the following: $settings['db'] = [ 'key' => 'val', 'nextKey' => 'nextVal',];
 */

// Init settings var
$settings = [];

// Bool if env is dev. Used in phpRenderer when rendering resources to break cache always for mobile
$settings['dev'] = false;

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
    'database' => 'slim_example_project',
    'username' => 'root',
    'password' => '',
    'driver' => \Cake\Database\Driver\Mysql::class,
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

$settings['logger'] = [
    'name' => 'app',
    'path' => __DIR__ . '/../logs',
    'filename' => 'app.log',
    'level' => \Monolog\Logger::DEBUG,
    'file_permission' => 0775,
];

return $settings;
