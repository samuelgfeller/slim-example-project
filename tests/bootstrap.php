<?php

// Turn on output buffering - while output buffering is active no output is sent from the script (other than headers),
// instead the output is stored in an internal buffer
//ob_start();

// Used in container-bootstrap
if (!defined('APP_ENV')) {
    // APP_ENV value has to be similar to the testing container file name
    // If test container is named "container.testing.php" APP_ENV has to be "testing"
    define('APP_ENV', 'testing');
}

return require __DIR__ . '/../app/bootstrap.php';