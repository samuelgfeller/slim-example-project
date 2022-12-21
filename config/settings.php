<?php

// Load default settings
$settings = require __DIR__ . '/defaults.php';

// Load secret configuration
if (file_exists(__DIR__ . '/../../env.php')) {
    require __DIR__ . '/../../env.php';
} elseif (file_exists(__DIR__ . '/env.php')) {
    require __DIR__ . '/env.php';
}

// Set APP_ENV if not already set
$_ENV['APP_ENV'] = $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? 'dev';

// Overwrite previous config with APP_ENV specific values ("env", "test" or "github")
if (isset($_ENV['APP_ENV'])) {
    $appEnvConfigFile = __DIR__ . '/env.' . $_ENV['APP_ENV'] . '.php';
    if (file_exists($appEnvConfigFile)) {
        // e.g. env.test.php
        require $appEnvConfigFile;
    }
}

echo "\nEnv: " . $_ENV['APP_ENV'] . " Mailer transport type: " . $settings['smtp']['type']."\n";

return $settings;
