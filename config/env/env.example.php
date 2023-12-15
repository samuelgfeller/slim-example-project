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

// $_ENV['APP_ENV'] should be set to "prod" in the secret env.php file of the prod server.
// APP_ENV should NOT be set to "dev" in dev env because that would override the phpunit.xml APP_ENV setting.

// Database
$settings['db']['host'] = 'localhost';
$settings['db']['username'] = 'root';
$settings['db']['password'] = '';
