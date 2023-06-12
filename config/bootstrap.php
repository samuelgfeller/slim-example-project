<?php

use DI\ContainerBuilder;
use Slim\App;

require __DIR__ . '/../vendor/autoload.php';
set_language('de_CH');
// setlocale(LC_ALL, 'de-DE');

// Instantiate DI ContainerBuilder
$containerBuilder = new ContainerBuilder();

// Add container definitions
$containerBuilder->addDefinitions(__DIR__ . '/container.php');

// Build DI Container instance
$container = $containerBuilder->build();

// Create App instance
return $container->get(App::class);
