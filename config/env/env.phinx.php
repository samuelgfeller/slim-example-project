<?php
/**
 * Phinx config
 * In separate file to add PDO instance to config and these additional
 * settings are appended as parameter the command.
 */

use Cake\Core\Configure;
use Slim\App;

/** @var App $app */
$app = require __DIR__ . '/../bootstrap.php';

Configure::write('App.namespace', 'App');

$container = $app->getContainer();
$pdo = $container->get(PDO::class);
$config = $container->get('settings');
$database = $config['db']['database'];

$phinxConfig = $config['phinx'];

$phinxConfig['environments']['local'] = [
    // Set database name
    'name' => $database,
    'connection' => $pdo,
];

return $phinxConfig;
