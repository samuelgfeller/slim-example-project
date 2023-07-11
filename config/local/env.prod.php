<?php
/**
 * Production specific configuration values.
 *
 * For these settings to be taken into account in production,
 * $_ENV['APP_ENV'] must be set to "prod" in the env.php file of the productive server.
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

$settings['deployment']['update_imports_version'] = false;

$settings['db']['database'] = 'samuelgfeller_demo_slim_example_project';
