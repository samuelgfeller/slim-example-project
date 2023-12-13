<?php
/**
 * Execute a class function instantiated by the container with the command line.
 * The first argument is the container key and the second the function name that should be called.
 * Example: php bin/console.php SqlSchemaGenerator generateSqlSchema
 */

// Boot the application
$app = require __DIR__ . '/../config/bootstrap.php';

/** Get the container instance @var Psr\Container\ContainerInterface $container */
$container = $app->getContainer();

// The $argv variable is an array that contains the command-line arguments passed to the script.
// The first element of the $argv array is always the name of the script itself ('bin/console.php').
// array_shift($argv) removes this first element that is not relevant here.
array_shift($argv);

// The now first parameter after the script name that was removed is the class name.
// The second element in the $argv array is the function name.
[$containerKey, $functionName] = $argv;

// Retrieve the instance corresponding to the $containerKey form the container.
$objectInstance = $container->get($containerKey);

// The call_user_func_array function is used to call the specified function on the retrieved instance.
// In this case, it's calling the function specified by $functionName on the object instance.
// The second parameter is an empty array, which means no parameters are passed to the function.
call_user_func_array([$objectInstance, $functionName], []);
