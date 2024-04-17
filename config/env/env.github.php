<?php

// Add all test settings

// MUST NOT be require_once otherwise test settings are included only once and not again for the next tests
require __DIR__ . '/env.test.php';

// Database
$settings['db']['host'] = '127.0.0.1';
$settings['db']['database'] = 'slim_example_project_test';
$settings['db']['username'] = 'root';
$settings['db']['password'] = 'root';
