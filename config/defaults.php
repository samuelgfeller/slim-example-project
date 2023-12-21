<?php
/**
 * Default configuration values.
 *
 * This file should contain all keys even secret ones to serve as template
 *
 * This is the first file loaded in settings.php and can as such safely define arrays
 * without the risk of overwriting something.
 * Permitted to do the following: $settings['db'] = ['key' => 'val', 'nextKey' => 'nextVal',];
 */

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');

// Timezone - time() is timezone independent https://stackoverflow.com/a/36390811/9013718
date_default_timezone_set('Europe/Zurich');
// Set default locale
setlocale(LC_ALL, 'en_US.utf8', 'en_US');

// Init settings var
$settings = [];

// Error handler
$settings['error'] = [
    // Should be set to false in production. When set to true, it will throw an ErrorException for notices and warnings.
    'display_error_details' => false,
    'log_errors' => true,
    'log_error_details' => true,
];

// Set false for production env
$settings['dev'] = false;

// Project root dir (1 parent)
$settings['root_dir'] = dirname(__DIR__, 1);

$settings['deployment'] = [
    // Version `null` or string. If JsImportCacheBuster is enabled, `null` removes all query param versions from js imports
    'version' => '0.4.0',
    // When true, JsImportCacheBuster is enabled and goes through all js files and changes the version number from the imports
    'update_js_imports_version' => true,
    // Disable in prod
    'assets_path' => $settings['root_dir'] . '/public/assets',
];

$settings['public'] = [
    'app_name' => 'Slim example Project',
    'email' => [
        'main_contact_address' => 'slim-example-project@samuel-gfeller.ch',
        'main_sender_address' => 'no-reply@samuel-gfeller.ch',
        'main_sender_name' => 'Slim example Project',
    ],
];

$settings['locale'] = [
    'translations_path' => $settings['root_dir'] . '/resources/translations',
    // When adding new available locales, new translated email templates have to be added as well in their
    // respective language subdirectory.
    'available' => ['en_US', 'de_CH', 'fr_CH'],
    'default' => 'en_US',
];

// Security

// Protection against rapid fire and password spraying force attacks
$settings['security'] = [
    // Bool if login requests should be throttled
    'throttle_login' => true,
    // Bool if email requests should be throttled
    'throttle_email' => true,
    // Seconds in the past relevant for global, user and ip login and email request throttle
    // If 3600, the requests in the past hour will be evaluated and compared to the set thresholds below
    'timespan' => 3600,

    // Key = sent email amount for throttling to apply; value = delay in seconds or 'captcha'; Lowest to highest
    'login_throttle_rule' => [4 => 10, 9 => 120, 12 => 'captcha'],
    // Percentage of login requests that may be failures (threshold) in the past month
    'login_failure_percentage' => 20,

    // Email configurations can be omitted if specific rule shouldn't be throttled
    // Key = sent email amount for throttling to apply; value = delay in seconds or 'captcha'; Lowest to highest
    // 'user_email_throttle_rule' => [5 => 2, 10 => 4, 20 => 'captcha'],
    // // Daily site-wide limit before throttling begins
    // 'global_daily_email_threshold' => 300,
    // // Mailgun offers 1000 free emails per month. Throttling should begin at 900.
    // 'global_monthly_email_threshold' => 900,
];

// Secret values are overwritten in env.php
$settings['db'] = [
    'host' => '127.0.0.1',
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
    // Turn off persistent connections
    'persistent' => false,
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
    ],
];

$settings['api'] = [
    // Url that is allowed to make api calls to this app
    'allowed_origin' => null,
];

// Phinx database migrations settings
$settings['phinx'] = [
    'paths' => [
        'migrations' => $settings['root_dir'] . '/resources/migrations',
        'seeds' => $settings['root_dir'] . '/resources/seeds',
    ],
    'schema_file' => $settings['root_dir'] . '/resources/schema/schema.php',
    'default_migration_prefix' => 'db_change_',
    'generate_migration_name' => true,
    'environments' => [
        // Table that keeps track of the migrations
        'default_migration_table' => 'phinx_migration_log',
        'default_environment' => 'local',
        'local' => [/* Environment specifics such as db credentials are added in env.phinx.php */],
    ],
];

// Template renderer settings
$settings['renderer'] = [
    // Template path
    'path' => $settings['root_dir'] . '/templates',
];

// Session
$settings['session'] = [
    'name' => 'slim-example-project',
    // 5h session lifetime
    'lifetime' => 18000, // Time in seconds
    // Sends cookie only over https
    'cookie_secure' => true,
    // Additional XSS protection
    // Cookie is not accessible in JavaScript
    'cookie_httponly' => false,
];

$settings['logger'] = [
    // Log file location
    'path' => $settings['root_dir'] . '/logs',
    // Default log level
    'level' => \Monolog\Level::Debug,
];

// Email settings
$settings['smtp'] = [
    // use type 'null' for the null adapter
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

return $settings;
