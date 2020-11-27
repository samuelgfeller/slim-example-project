<?php


error_reporting(E_ALL);
ini_set('display_errors', '1');

$settings['env'] = 'development';

// Database
$settings['db'] = [
    'host' => 'localhost',
    'database' => 'slim-api-example',
    'username' => 'Admin',
    'password' => '12345678',
];

$settings['jwt']['secret'] = 'secretPass';
