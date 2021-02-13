<?php
/**
 * Environment specific configuration values
 *
 * Make sure env.php file is added to .gitignore and ideally place the env.php outside
 * the project root directory, to protect against overwriting at deployment.
 *
 * How to set values
 * bad $settings['db'] = [ 'key' => 'val', 'nextKey' => 'nextVal',];
 * good $settings['db]['key'] = 'val'; $settings['db]['nextKey'] = 'nextVal';
 * It's mandatory to set every key by its own and not remap the entire array
 */

// False for production
// Present in development env.php to simulate production environment
$settings['dev'] = true;

error_reporting(E_ALL);
// In case error is not caught by error handler (below)
ini_set('display_errors', $settings['dev'] ? '1' : '0');

// Error handler. More controlled than ini
$settings['error']['display_error_details'] = $settings['dev'];

// Database
$settings['db']['host'] = 'localhost';
$settings['db']['database'] = 'slim_example_project';
$settings['db']['username'] = 'root';
$settings['db']['password'] = '';