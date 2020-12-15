<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

// SLE-57 Making warnings and notices to exceptions in dev
set_error_handler(function ($severity, $message, $file, $line) {
    if (error_reporting() & $severity) {
        throw new \ErrorException($message, 0, $severity, $file, $line);
    }
});

// It's mandatory to config every key by its own and not remap the entire array
// bad $settings['db'] = [ 'key' => 'val', 'nextKey' => 'nextVal', ]
// good $settings['db]['key'] = 'val';

// Error handler
$settings['error']['display_error_details'] = true;

// Env
$settings['env'] = 'development';

// Database
$settings['db']['host'] = 'localhost';
$settings['db']['database'] = 'slim-api-example';
$settings['db']['username'] = 'Admin';
$settings['db']['password'] = '12345678';

$settings['jwt']['secret'] = 'secretPass';

