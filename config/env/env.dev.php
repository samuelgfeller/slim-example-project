<?php
/**
 * Development-specific configuration values.
 *
 * How to set values:
 * Every key must be set by its own to not overwrite the entire array.
 * Correct: $settings['db]['key'] = 'val'; $settings['db]['nextKey'] = 'nextVal';
 * Incorrect $settings['db'] = [ 'key' => 'val', 'nextKey' => 'nextVal',];
 */

// Set false to show production error pages
$settings['dev'] = false;

// For the that the error is not caught by custom error handler (below)
ini_set('display_errors', $settings['dev'] ? '1' : '0');

// Display error details in browser and throw ErrorException for notices and warnings
$settings['error']['display_error_details'] = $settings['dev'];

// Database
$settings['db']['database'] = 'slim_example_project';

// When adding new values (above this comment), please refer to the section [How to set values] in the PHPDoc on top of the page
