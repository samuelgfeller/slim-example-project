<?php
/**
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

    <link rel="stylesheet" href="assets/general/css/default.css">
    <link rel="stylesheet" href="assets/general/css/layout.css">
    <link rel="stylesheet" href="assets/general/css/navbar.css">

    <title><?= $title ?></title>
</head>
<body>
<!-- "In terms of semantics, <div> is the best choice" as wrapper https://css-tricks.com/best-way-implement-wrapper-css -->
<div id="wrapper">
    <header>
        <nav class="clearfix">
            <span id="brand-name-span" class="cursor-pointer">Slim Example Project</span>
            <a href="<?= $route->urlFor('hello') ?>" <?= $uri->getPath() === $route->urlFor(
                'hello'
            ) ? 'class="is-active"' : '' ?>>Home</a>
            <a href="<?= $route->urlFor('user-list') ?>" <?= $uri->getPath() === $route->urlFor(
                'user-list'
            ) ? 'class="is-active"' : '' ?>>Users</a>
            <a href="<?= $route->urlFor('profile') ?>" <?= $uri->getPath() === $route->urlFor(
                'profile'
            ) ? 'class="is-active"' : '' ?>>Profile</a>
            <a href="<?= $route->urlFor('post-list-own') ?>" <?= $uri->getPath() === $route->urlFor(
                'post-list-own'
            ) ? 'class="is-active"' : '' ?>>Own posts</a>
            <a href="<?= $route->urlFor('post-list-all') ?>" <?= $uri->getPath() === $route->urlFor(
                'post-list-all'
            ) ? 'class="is-active"' : '' ?>>All posts</a>
            <a href="<?= $route->urlFor('login-page') ?>" <?= $uri->getPath() === $route->urlFor(
                'login-page'
            ) ? 'class="is-active"' : '' ?>>Login</a>
            <a href="<?= $route->urlFor('register-page') ?>" <?= $uri->getPath() === $route->urlFor(
                'register-page'
            ) ? 'class="is-active"' : '' ?>>Register</a>

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
        <?= $content ?>
    </main>

    <footer>
        <address>Made with <img src="assets/general/img/heart-icon.svg" alt="heart icon" class="footer-icon"> <a
                    href="https://github.com/samuelgfeller/slim-example-project" class="no-style-a" target="_blank">
                by Samuel Gfeller <img src="assets/general/img/github-icon.svg" alt="github icon" id="github-icon"
                     class="footer-icon"></a></address>
    </footer>

</div>

<script src="assets/general/js/default.js"></script>
<script src="assets/general/js/navbar.js"></script>
</body>
</html>

