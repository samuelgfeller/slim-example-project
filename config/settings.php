<?php

// Defaults
$settings = require __DIR__ . '/defaults.php';

// Load environment configuration
if (file_exists(__DIR__ . '/../../env.php')) {
    require __DIR__ . '/../../env.php';
} elseif (file_exists(__DIR__ . '/env.php')) {
    require __DIR__ . '/env.php';
}

// Overwrite previous config with integration testing values if APP_ENV is set to 'testing'
if (isset($_ENV['APP_ENV'])) {
    //  env.testing.php
    require __DIR__ . '/env.' . $_ENV['APP_ENV'] . '.php';
}

return $settings;
