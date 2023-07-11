<?php

// Add all test settings
// MUST NOT be require_once as otherwise test settings such as mailer transport type is
// only included once and not again for the next tests
// (tested by echoing $settings['smtp']['type'] after settings arr is built in settings.php)
require __DIR__ . '/env.test.php';

// Database
$settings['db']['host'] = '127.0.0.1';
$settings['db']['database'] = 'slim_example_project_test';
$settings['db']['username'] = 'root';
$settings['db']['password'] = 'root';
