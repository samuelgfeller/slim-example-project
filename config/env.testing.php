<?php

// Change database name for integration testing
$settings['db']['database'] = 'slim_example_project_test';

// Optional setting but used in unit test
$settings['security']['global_daily_email_threshold'] = 300;
$settings['security']['global_monthly_email_threshold'] = 1000;
// Successful requests throttling has to be enabled as it's tested
$settings['security']['throttle_login_success'] = true;

// Using the null adapter to prevent emails from actually being sent
$settings['smtp']['type'] = 'null';
