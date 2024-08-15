<?php
/**
 * Default configuration values.
 *
 * This file should contain all keys, even secret ones to serve as template.
 *
 * This is the first file loaded in settings.php and can safely define arrays
 * without the risk of overwriting something.
 * The only file where the following is permitted: $settings['db'] = ['key' => 'val', 'nextKey' => 'nextVal'];
 *
 * Documentation: https://samuel-gfeller.ch/docs/Configuration.
 */

// Timezone - time() is timezone independent https://stackoverflow.com/a/36390811/9013718
date_default_timezone_set('Europe/Zurich');

// Set default locale
setlocale(LC_ALL, 'en_US.utf8', 'en_US');

// Init settings var
$settings = [];

// Project root dir (1 parent)
$settings['root_dir'] = dirname(__DIR__, 1);

// Error handling
// Documentation: https://samuel-gfeller.ch/docs/Error-Handling
$settings['error'] = [
    // MUST be set to false in production.
    // When set to true, it shows error details and throws an ErrorException for notices and warnings.
    'display_error_details' => false,
    'log_errors' => true,
];

// Deployment settings
$settings['deployment'] = [
    // Version string or null. If JsImportCacheBuster is enabled, `null` removes all query param versions from js
    // imports.
    'version' => '1.0.0',
    // When true, JsImportCacheBuster is enabled and goes through all js files and changes the version number
    // from the imports. Should be disabled in env.prod.php.
    // https://samuel-gfeller.ch/docs/Template-Rendering#js-import-cache-busting
    'update_js_imports_version' => false,
    // Asset path required by the JsImportCacheBuster
    'asset_path' => $settings['root_dir'] . '/public/assets',
];

$settings['public'] = [
    'app_name' => 'Slim Example Project',
    'email' => [
        'main_contact_email' => 'slim-example-project@samuel-gfeller.ch',
        'main_sender_address' => 'no-reply@samuel-gfeller.ch',
        'main_sender_name' => 'Slim Example Project',
    ],
];

// Translations: https://samuel-gfeller.ch/docs/Translations
$settings['locale'] = [
    'translations_path' => $settings['root_dir'] . '/resources/translations',
    // When adding new available locales, new translated email templates have to be added as well in their
    // respective language subdirectory.
    'available' => ['en_US', 'de_CH', 'fr_CH'],
    'default' => 'en_US',
];

// Protection against rapid fire and password spraying force attacks
// Docs: https://samuel-gfeller.ch/docs/Security#request-throttling
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
    'user_email_throttle_rule' => [5 => 2, 10 => 4, 20 => 'captcha'],
    // Daily site-wide limit before throttling begins
    'global_daily_email_threshold' => 300,
    // Mailgun offers 1000 free emails per month, so throttling should begin at 900.
    'global_monthly_email_threshold' => 900,
];

// Database
// Docs: https://samuel-gfeller.ch/docs/Repository-and-Query-Builder
$settings['db'] = [
    'host' => '127.0.0.1',
    'database' => 'slim_example_project',
    'username' => 'root',
    'password' => '',
    'driver' => Cake\Database\Driver\Mysql::class,
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

// API endpoint settings
// Docs: https://samuel-gfeller.ch/docs/API-Endpoint
$settings['api'] = [
    // Url that is allowed to make api calls to this app
    'allowed_origin' => null,
];

// Phinx database migrations settings
// Docs: https://samuel-gfeller.ch/docs/Database-Migrations
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
        'local' => [
            /* Environment specifics such as db credentials from the secret config are added in env.phinx.php */
        ],
    ],
];

// Template renderer settings
// Docs: https://samuel-gfeller.ch/docs/Template-Rendering
$settings['renderer'] = [
    // Template path
    'path' => $settings['root_dir'] . '/templates',
];

// Session
// Docs: https://samuel-gfeller.ch/docs/Session-and-Flash-messages
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

// Logger
// Docs: https://samuel-gfeller.ch/docs/Logging
$settings['logger'] = [
    // Log file location
    'path' => $settings['root_dir'] . '/logs',
    // Default log level
    'level' => Monolog\Level::Debug,
];

// Email settings
// Docs: https://samuel-gfeller.ch/docs/Mailing
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
