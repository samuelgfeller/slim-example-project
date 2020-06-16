<?php
// Container bootstrap

$container = [];


$container = array_merge($container, require __DIR__ . '/container.php');
$container = array_merge($container, require __DIR__ . '/repositories.php');


// Load the APP_ENV specific container
if (defined('APP_ENV')) {
    if (file_exists(__DIR__ . '/container.' . APP_ENV . '.php')) {
        $container = array_merge($container, require __DIR__ . '/container.' . APP_ENV . '.php');
    }
}


// To apply APP_CONFIG=dev in console
/*if (getenv('APP_CONFIG') && file_exists(__DIR__ . '/container.' . getenv('APP_CONFIG') . '.php')) {
    require __DIR__ . '/container.' . getenv('APP_CONFIG') . '.php';
}*/


return $container;