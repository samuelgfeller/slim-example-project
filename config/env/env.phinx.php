<?php
/**
 * Phinx configuration where PDO connection instance is retrieved from the container
 * and is added to the 'local' phinx configuration.
 *
 * Migrate command: vendor/bin/phinx migrate -c config/env/env.phinx.php --ansi -vvv
 * Generate migration: phinx-migrations generate --overwrite -c config/env/env.phinx.php --ansi
 *
 * Documentation: https://samuel-gfeller.ch/docs/Database-Migrations
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
