<?php
/**
 * Environment specific configuration values.
 *
 * Make sure env.php file is added to .gitignore and ideally place the env.php outside
 * the project root directory, to protect against overwriting at deployment.
 *
 * How to set values
 * Each key and value should be added in defaults.php as well to serve as template
 * bad $settings['db'] = [ 'key' => 'val', 'nextKey' => 'nextVal',];
 * good $settings['db]['key'] = 'val'; $settings['db]['nextKey'] = 'nextVal';
 * It's mandatory to set every key by its own and not remap the entire array
 */

// False for production
// When true, resources are not stored in cache (for mobile testing) and error details are displayed
$settings['dev'] = true;

error_reporting(E_ALL);
// In case error is not caught by error handler (below)
ini_set('display_errors', $settings['dev'] ? '1' : '0');

// Error handler. More controlled than ini
$settings['error']['display_error_details'] = $settings['dev'];

// null or string. When null, all query param versions from js imports are removed
$settings['deployment']['version'] = '0.1';
$settings['deployment']['assetsPath'] = __DIR__ . '/../public/assets';

// Database
$settings['db']['host'] = 'localhost';
$settings['db']['database'] = 'slim_example_project';
$settings['db']['username'] = 'root';
$settings['db']['password'] = '';

// Email
$settings['smtp']['host'] = 'smtp.example.com';
$settings['smtp']['username'] = 'user@example.com';
$settings['smtp']['password'] = 'secret';
// Change to `true` to test prod email server (not using `$settings['dev']` to see exceptions using prod email server)
if ($useProdServer = false) {
    // Prod smtp server
    $settings['smtp']['host'] = 'smtp.example.com';
    $settings['smtp']['username'] = 'user@example.com';
    $settings['smtp']['password'] = 'secret';
}

// When adding new values (above this comment), please refer to the section [How to set values] in the PHPDoc on top of the page
