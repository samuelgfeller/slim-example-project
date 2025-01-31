<?php

/**
 * Start up the Slim App.
 *
 * Documentation: https://samuel-gfeller.ch/docs/Web-Server-config-and-Slim-Bootstrapping#slim-bootstrapping
 */

use DI\ContainerBuilder;
use Slim\App;

require __DIR__ . '/../vendor/autoload.php';

// Instantiate DI ContainerBuilder
$containerBuilder = new ContainerBuilder();
// Add container definitions and build DI container
$container = $containerBuilder->addDefinitions(__DIR__ . '/container.php')->build();

// Create app instance
return $container->get(App::class);
