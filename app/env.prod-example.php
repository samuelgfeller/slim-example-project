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

// error_reporting taken from server php.ini
// display_errors value defined in server

// Error handler. More controlled than ini
$settings['error']['display_error_details'] = false;

// Database
$settings['db']['host'] = 'localhost';
$settings['db']['database'] = 'slim-example-project';
$settings['db']['username'] = 'Admin';
$settings['db']['password'] = '12345678';





