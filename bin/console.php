<?php
/**
 * Execute a class function instantiated by the container with the command line.
 * The first argument is the container key and the second the function name that should be called.
 * Example: php bin/console.php DatabaseSqlSchemaGenerator generateSqlSchema
 * This could easily be extended with an additional parameter to call function that are
 * not part of the container definitions.
 */

use Psr\Container\ContainerInterface;

require_once __DIR__ . '/../vendor/autoload.php';

/** @var ContainerInterface $container */
$container = (require __DIR__ . '/../config/bootstrap.php')->getContainer();

// position [0] is the script's file name
array_shift($argv);
$className = array_shift($argv);
$funcName = array_shift($argv);

$objectInstance = $container->get($className);
call_user_func_array([$objectInstance, $funcName], []);
