<?php

// MUST be "require"; not require_once otherwise test settings are not included for the tests cases after the first one

// Load default settings
$settings = require __DIR__ . '/defaults.php';

// Load secret configuration
if (file_exists(__DIR__ . '/../../env.php')) {
    require __DIR__ . '/../../env.php'; // Take env outside of project if existing
} elseif (file_exists(__DIR__ . '/env/env.php')) {
    require __DIR__ . '/env/env.php';
}

// Set APP_ENV if not already set
$_ENV['APP_ENV'] = $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? 'dev';

// Overwrite previous config with APP_ENV specific values ("env", "test" or "github")
if (isset($_ENV['APP_ENV'])) {
    $appEnvConfigFile = __DIR__ . '/env/env.' . $_ENV['APP_ENV'] . '.php';
    if (file_exists($appEnvConfigFile)) {
        // e.g. env.test.php
        require $appEnvConfigFile;
    }
}

return $settings;
