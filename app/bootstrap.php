<?php

// @todo remove that
ini_set('xdebug.var_display_max_depth', '10');
ini_set('xdebug.var_display_max_children', '256');
ini_set('xdebug.var_display_max_data', '1024');

// todo do I have to filter input data?
// todo maybe put permission validation in a better place like UserValidation class?
// todo testing junit
// todo translation
// todo https middleware https://odan.github.io/2020/04/07/slim4-https-middleware.html
// todo use frontend framework

use Psr\Log\LoggerInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use DI\ContainerBuilder;

require __DIR__ . '/../vendor/autoload.php';

// Instantiate PHP-DI ContainerBuilder
$containerBuilder = new ContainerBuilder();

// Add container definitions
$containerBuilder->addDefinitions(__DIR__ . '/container/container.php');

// Set up and add repositories
$containerBuilder->addDefinitions(__DIR__ . '/container/repositories.php');

// Build PHP-DI Container instance
$container = $containerBuilder->build();

// Create App instance
$app = $container->get(App::class);

// Register middleware
$middleware = require __DIR__ . '/container/middleware.php';
$middleware($app);

// Routing
$routes = require __DIR__ . '/../app/routes.php';
$routes($app);

return $app;

