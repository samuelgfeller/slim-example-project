<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

$settings['env'] = 'development';

// It's mandatory to config every key by its own and not remap the entire array like this $settings['db'] = [ 'key' => 'val', 'nextKey' => 'nextVal', ]
// Database
$settings['db']['host'] = 'localhost';
$settings['db']['database'] = 'slim-api-example';
$settings['db']['username'] = 'Admin';
$settings['db']['password'] = '12345678';

$settings['jwt']['secret'] = 'secretPass';

