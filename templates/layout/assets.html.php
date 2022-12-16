<?php
/**
 * CSS and Javascript resources
 * @var $stylesheets array of stylesheet paths
 * @var $scripts array of script paths
 * @var $version null|string app version
 */

//$dev = false;
// CSS stylesheets
foreach ($stylesheets ?? [] as $stylesheet) {
    // "assets/" not default since some paths could be external urls
    // If dev, then time is appended to break cache always (especially for mobile)
    // echo '<link rel="stylesheet" type="text/css" href="' . $stylesheet . ($dev ? '?t=' . time() : '') . '">';
    echo '<link rel="stylesheet" type="text/css" href="' . $stylesheet . ($version ? '?v='. $version : '') . '">';
}

// Javascript files
foreach ($scripts ?? [] as $script) {
    // "assets/" not default since some paths could be external urls
    // Default use of defer because it allows faster parsing and less bugs [SLE-77]
    // If dev, then time is appended to break cache always (especially for mobile)
    echo '<script defer src="' . $script . ($version ? '?v='. $version : '') . '"></script>';
}

// Javascript module files
foreach ($jsModules ?? [] as $modulePath) {
    // "assets/" not default since some paths could be external urls
    // Default use of defer because it allows faster parsing and less bugs [SLE-77]
    // If dev, then time is appended to break cache always (especially for mobile)
    echo '<script defer type="module" src="' . $modulePath . ($version ? '?v='. $version : '') . '"></script>';
}
