<?php
/**
 * Development specific configuration values.
 *
 * How to set values
 * bad $settings['db'] = [ 'key' => 'val', 'nextKey' => 'nextVal',];
 * good $settings['db]['key'] = 'val'; $settings['db]['nextKey'] = 'nextVal';
 * It's mandatory to set every key individually and not remap the entire array
 */

// Set false to show production error pages
$settings['dev'] = true;

error_reporting(E_ALL);
// In case error is not caught by error handler (below)
ini_set('display_errors', $settings['dev'] ? '1' : '0');

// Error handler. More controlled than ini
$settings['error']['display_error_details'] = $settings['dev'];

$settings['deployment']['assetsPath'] = __DIR__ . '/../public/assets';

// Database
$settings['db']['database'] = 'slim_example_project';

// When adding new values (above this comment), please refer to the section [How to set values] in the PHPDoc on top of the page
