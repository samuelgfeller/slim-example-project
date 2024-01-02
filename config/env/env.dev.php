<?php
/**
 * Development specific configuration values.
 */

// Set false to show production error pages
$settings['dev'] = true;

// In case error is not caught by error handler (below)
ini_set('display_errors', $settings['dev'] ? '1' : '0');

// Error handler. More controlled than ini
$settings['error']['display_error_details'] = $settings['dev'];

// Database
$settings['db']['database'] = 'slim_example_project';

// When adding new values (above this comment), please refer to the section [How to set values] in the PHPDoc on top of the page
