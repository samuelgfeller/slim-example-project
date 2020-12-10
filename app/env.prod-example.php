<?php

// error_reporting taken from server php.ini
// display_errors value defined in server

$settings['env'] = 'development';

// Error handling
$settings['error']['display_error_details'] = false;


// Database
$settings['db'] = [
    'host' => 'localhost',
    'database' => 'slim-api-example',
    'username' => 'Admin',
    'password' => '12345678',
];

$settings['jwt']['secret'] = 'secretPass';
