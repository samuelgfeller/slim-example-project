<?php
/**
 * @var string $basePath
 * @var string $title
 * @var \Slim\Interfaces\RouteParserInterface $route
 * @var \Psr\Http\Message\UriInterface $uri
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
<div id="wrapper">
    <div id="header">
        <!--Nav-->
        <div id="nav" class="clearfix">
            <span id="brand-name-span" class="cursorPointer">Slim Example Project</span>
            <?php
//            foreach ($routes as $name => $route) {
//                echo '<a href="' . $route['link'] . '" ' . ($route['active'] ? 'class="is-active"' : '') . ' data-active-color="' . $route['color'] . '">' . $name . '</a>';
//
//            }
            ?>

            <a href="<?= $route->urlFor('hello') ?>" <?= $uri->getPath() === $route->urlFor('hello') ?
                'class="is-active"' : '' ?>>Home</a>
            <a href="<?= $route->urlFor('user-list') ?>" <?= $uri->getPath() === $route->urlFor('user-list') ?
                'class="is-active"' : '' ?>>Users</a>
            <a href="<?= $route->urlFor('profile') ?>" <?= $uri->getPath() === $route->urlFor('profile') ?
                'class="is-active"' : '' ?>>Profile</a>
            <a href="<?= $route->urlFor('post-list-own') ?>" <?= $uri->getPath() === $route->urlFor('post-list-own') ?
                'class="is-active"' : '' ?>>Own posts</a>
            <a href="<?= $route->urlFor('post-list-all') ?>" <?= $uri->getPath() === $route->urlFor('post-list-all') ?
                'class="is-active"' : '' ?>>All posts</a>
            <a href="<?= $route->urlFor('login-page') ?>" <?= $uri->getPath() === $route->urlFor('login-page') ?
                'class="is-active"' : '' ?>>Login</a>
            <a href="<?= $route->urlFor('register-page') ?>" <?= $uri->getPath() === $route->urlFor('register-page') ?
                'class="is-active"' : '' ?>>Register</a>

            <div id="nav-icon">
                <span></span>
                <span></span>
                <span></span>
                <span></span>
            </div>
            <span class="nav-indicator noAnimationOnPageLoad" id="nav-indicator"></span>
        </div>
    </div>

    <div id="pageContent">

        <?= $content ?>
    </div>

    <div id="footer">

    </div>
</div>

<script src="assets/general/js/default.js"></script>
<script src="assets/general/js/navbar.js"></script>
</body>
</html>

