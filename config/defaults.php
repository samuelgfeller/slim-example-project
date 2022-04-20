<?php
/**
 * Default configuration values
 *
 * This file should contain all keys even secret ones to serve as template
 *
 * This is the first file loaded in settings.php and can as such safely define arrays
 * without the risk of overwriting something.
 * Permitted to do the following: $settings['db'] = [ 'key' => 'val', 'nextKey' => 'nextVal',];
 */

// Init settings var
$settings = [];

// Bool if env is dev. Used in phpRenderer when rendering resources to break cache always for mobile
$settings['dev'] = false;

// Error handler
$settings['error'] = [
    // Should be set to false in production
    'display_error_details' => false,
    // Should be set to false for unit tests
    'log_errors' => true,
    // Display error details in error log
    'log_error_details' => true,
];

// Secret values are overwritten in env.php
$settings['db'] = [
    'host' => 'localhost',
    'database' => 'slim_example_project',
    'username' => 'root',
    'password' => '',
    'driver' => \Cake\Database\Driver\Mysql::class,
    'encoding' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    // Enable identifier quoting
    'quoteIdentifiers' => true,
    // Disable query logging
    'log' => false,
    // PDO options
    'flags' => [
        // Turn off persistent connections
        PDO::ATTR_PERSISTENT => false,
        // Enable exceptions
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        // Emulate prepared statements
        PDO::ATTR_EMULATE_PREPARES => true,
        // Set default fetch mode to array
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
];

// Security
$settings['security'] = [
    /**
     * Protection against rapid fire and distributed brute force attacks
     * If changed, RequestTrackFixture has to be updated accordingly
     */
    // Seconds in the past relevant for global, user and ip request throttle
    // If 3600, the requests in the past hour will be evaluated and compared to the set thresholds below
    'timespan' => 3600,

    // key = request amount (fail: x + 1 as check is done at beginning of next request); value = delay; Lowest to highest
    // ! When changed, update RequestTrackProvider and RequestTrackFixture as well
    // Login threshold and matching throttle concerning specific user or coming from same ip (successes and failures)
    // If threshold is 4, there need to be already 4 failures for the check to fail as it's done before evaluating the
    // login request, the next check will be at the beginning of the the 5th
    'login_throttle' => [4 => 10, 9 => 120, 12 => 'captcha'],
    'user_email_throttle' => [5 => 2, 10 => 4, 20 => 'captcha'],

    // Percentage of login requests that may be failures (threshold)
    'login_failure_percentage' => 20,

    'global_daily_email_threshold' => 300, // optional
    // Mailgun offer 1250 free emails per month so 1k before throttling seems reasonable
    'global_monthly_email_threshold' => 1000, // optional
];

// Template renderer settings
$settings['renderer'] = [
    // Template path
    'path' => __DIR__ . '/../templates',
];

// Session
$settings['session'] = [
    'name' => 'webapp',
    'cache_expire' => 0,
];

$settings['logger'] = [
    'name' => 'app',
    'path' => __DIR__ . '/../logs',
    'filename' => 'app.log',
    'level' => \Monolog\Logger::DEBUG,
    'file_permission' => 0775,
];

// Email settings
$settings['smtp'] = [
    // use 'null' for the null adapter
    'type' => 'smtp',
    'host' => 'smtp.mailtrap.io',
    'port' => '587', // TLS: 587; SSL: 465
    'username' => 'my-username',
    'password' => 'my-secret-password',
];

$settings['google'] = [
    // reCAPTCHA secret key
    'recaptcha' => 'secretKey',
];

$settings['public'] = [
    'email' => [
        'main_contact_address' => 'slim-example-project@samuel-gfeller.ch',
        'main_sender_address' => 'no-reply@samuel-gfeller.ch',
        'main_sender_name' => 'Slim example Project',
    ],
];

return $settings;
