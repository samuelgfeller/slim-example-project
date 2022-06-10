<?php
/**
 * @var \Slim\Views\PhpRenderer $this
 * @var string $basePath
 * @var string $content PHP-View var page content
 * @var \Slim\Interfaces\RouteParserInterface $route
 * @var \Psr\Http\Message\UriInterface $uri
 * @var string $title
 */

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!--  Trailing slash has to be avoided on asset paths. Otherwise <base> does not work  -->
    <base href="<?= $basePath ?>/"/>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="assets/favicon.ico" type="image/x-icon"/>

    <?php
    // Define layout assets
    $layoutCss = [
        'assets/general/css/default.css',
        'assets/general/css/general.css',
        'assets/general/css/layout.css',
        'assets/general/css/navbar.css',
        'assets/general/css/flash.css'
    ];
    $layoutJs = [
        'https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js' /* Will be removed with SLE-81 */,
        'assets/general/js/default.js',
        'assets/general/js/general.js',
        'assets/general/js/navbar.js'
    ];

    // fetch() includes another template into the current template
    // Include template which contains HTML to include assets
    echo $this->fetch(
        'layout/assets.html.php', // Merge layout assets and from sub templates
        ['stylesheets' => array_merge($layoutCss, $css ?? []), 'scripts' => array_merge($layoutJs, $js ?? [])]
    );
    ?>

    <title><?= $title ?></title>
</head>
<body>
<!-- "In terms of semantics, <div> is the best choice" as wrapper https://css-tricks.com/best-way-implement-wrapper-css -->
<div id="wrapper">
    <header>
        <nav class="clearfix">
            <span id="brand-name-span" class="cursor-pointer">Slim Example Project</span>
            <a href="<?= $route->urlFor('home-page') ?>" <?= $uri->getPath() === $route->urlFor(
                'home-page'
            ) ? 'class="is-active"' : '' ?>>Dashboard</a>
            <a href="<?= $route->urlFor('user-list') ?>" <?= $uri->getPath() === $route->urlFor(
                'user-list'
            ) ? 'class="is-active"' : '' ?>>Non-assigned requests</a>
            <a href="<?= $route->urlFor('client-list-assigned-to-me-page') ?>" <?= $uri->getPath() === $route->urlFor(
                'client-list-assigned-to-me-page'
            ) ? 'class="is-active"' : '' ?>>Assigned to me</a>
            <a href="<?= $route->urlFor('client-list-all-page') ?>" <?= $uri->getPath() === $route->urlFor(
                'client-list-all-page'
            ) ? 'class="is-active"' : '' ?>>Client list</a>
            <a href="<?= $route->urlFor('client-list-all-page') ?>" <?= $uri->getPath() === $route->urlFor(
                'client-list-all-page'
            ) ? 'class="is-active"' : '' ?>>Admin area</a>
            <!--           <a href="<?/*= $route->urlFor('register-page') */?>" <?/*= $uri->getPath() === $route->urlFor(
                'register-page'
            ) ? 'class="is-active"' : '' */?>>Register</a>-->
            <a href="<?= $route->urlFor('profile-page') ?>" <?= $uri->getPath() === $route->urlFor(
                'profile-page'
            ) || $uri->getPath() === $route->urlFor('change-password-page') ? 'class="is-active"' : '' ?>>
                Profile</a>

            <div id="nav-icon">
                <span></span>
                <span></span>
                <span></span>
                <span></span>
            </div>
            <span class="nav-indicator no-animation-on-page-load" id="nav-indicator"></span>
        </nav>
    </header>

    <main>
        <?= $this->fetch('layout/flash-messages.html.php') ?>
        <?= $content ?>
        <?= $this->fetch('layout/request-throttle.html.php') ?>
    </main>

    <footer>
        <address>Made with <img src="assets/general/img/heart-icon.svg" alt="heart icon" class="footer-icon"> by <a
                    href="https://samuel-gfeller.ch" class="no-style-a" target="_blank">
                Samuel Gfeller <img src="assets/general/img/github-icon.svg" alt="github icon" id="github-icon"
                                    class="footer-icon"></a></address>
    </footer>

</div>

</body>
</html>

