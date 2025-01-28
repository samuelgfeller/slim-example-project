<?php

// Enable fatal ErrorException for notices and warnings (exception heavy)
$settings['error']['display_error_details'] = true;

// Database for integration testing must include the word "test"
$settings['db']['database'] = 'slim_example_project_test';

// Optional setting but used in unit test
$settings['security']['global_daily_email_threshold'] = 300;
$settings['security']['global_monthly_email_threshold'] = 1000;

// Using the null adapter to prevent emails from actually being sent
$settings['smtp']['type'] = 'null';

// Add example.com domain to allowed origin to test CORS in API call
$settings['api']['allowed_origin'] = 'https://example.com/';

// Enable test mode for the logger
$settings['logger']['test'] = true;
