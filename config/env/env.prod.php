<?php
/**
 * Production-specific configuration values.
 *
 * For these settings to be taken into account in production,
 * $_ENV['APP_ENV'] must be set to "prod" in the env.php file of the productive server.
 *
 * How to set values:
 * Every key must be set by its own to not overwrite the entire array.
 * Correct: $settings['db]['key'] = 'val'; $settings['db]['nextKey'] = 'nextVal';
 * Incorrect $settings['db'] = [ 'key' => 'val', 'nextKey' => 'nextVal',];
 */

// Error handler. More controlled than ini
$settings['error']['display_error_details'] = false;

// Disable update of JS imports version in production
$settings['deployment']['update_js_imports_version'] = false;

$settings['db']['database'] = 'samuelgfeller_demo_slim_example_project';

$settings['logger']['level'] = Monolog\Level::Info;
