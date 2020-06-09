<?php

// Turn on output buffering - while output buffering is active no output is sent from the script (other than headers),
// instead the output is stored in an internal buffer
ob_start();

//if (!defined('APP_ENV')) {
//    define('APP_ENV', 'integration');
//}

return require __DIR__ . '/../app/bootstrap.php';