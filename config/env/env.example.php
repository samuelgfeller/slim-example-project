<?php
/**
 * Secret environment specific configuration values.
 *
 * Make sure this env.php file is added to .gitignore and ideally place it outside
 * the project root directory.
 *
 * Every key must be set by its own to not overwrite the entire array.
 * Correct: $settings['db]['key'] = 'val'; $settings['db]['nextKey'] = 'nextVal';
 * Incorrect: $settings['db'] = [ 'key' => 'val', 'nextKey' => 'nextVal',];
 */

// $_ENV['APP_ENV'] should be set to "prod" in the secret env.php file on the prod server.
// APP_ENV must NOT be set to "dev" in the development secret env.php as it's already the default value
// and would override the phpunit.xml APP_ENV "test" setting.

// Database
$settings['db']['host'] = 'localhost';
$settings['db']['username'] = 'root';
$settings['db']['password'] = '';
