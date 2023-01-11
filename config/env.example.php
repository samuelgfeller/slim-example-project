<?php
/**
 * Secret environment specific configuration values.
 *
 * Make sure env.php file is added to .gitignore and ideally place the env.php outside
 * the project root directory, to protect against overwriting at deployment.
 *
 * How to set values
 * good $settings['db]['key'] = 'val'; $settings['db]['nextKey'] = 'nextVal';
 * bad $settings['db'] = [ 'key' => 'val', 'nextKey' => 'nextVal',];
 */

// ONLY in the env.php that is used in production, set the APP_ENV to "prod". Default is "dev" env, set in settings.php.
// $_ENV['APP_ENV'] = 'prod';

// Database
$settings['db']['host'] = 'localhost';
$settings['db']['username'] = 'root';
$settings['db']['password'] = '';
