<?php
/**
 * CSS and Javascript resources
 * @var $stylesheets array of stylesheet paths
 * @var $scripts array of script paths
 * @var $dev bool true for development false for production
 */


// CSS stylesheets
foreach ($stylesheets ?? [] as $stylesheet) {
    // "assets/" not default since some paths could be external urls
    // If dev, then time is appended to break cache always (especially for mobile)
    echo '<link rel="stylesheet" type="text/css" href="' . $stylesheet . ($dev ? '?t=' . time() : '') . '">';
}

// Javascript files
foreach ($scripts ?? [] as $script) {
    // "assets/" not default since some paths could be external urls
    // Default use of defer because it allows faster parsing and less bugs [SLE-77]
    // If dev, then time is appended to break cache always (especially for mobile)
    echo '<script defer src="' . $script . ($dev ? '?t=' . time() : '') . '"></script>';
}

// Javascript module files
foreach ($jsModules ?? [] as $modulePath) {
    // "assets/" not default since some paths could be external urls
    // Default use of defer because it allows faster parsing and less bugs [SLE-77]
    // If dev, then time is appended to break cache always (especially for mobile)
    echo '<script defer type="module" src="' . $modulePath . ($dev ? '?t=' . time() : '') . '"></script>';
}

