<?php
/**
 * @var \Slim\Views\PhpRenderer $this
 * @var string $basePath
 * @var string $content PHP-View var page content
 * @var \Slim\Interfaces\RouteParserInterface $route
 * @var string $currRouteName current route name
 * @var \Psr\Http\Message\UriInterface $uri
 * @var string $title
 * @var bool $userListAuthorization if user is allowed to read other users
 */

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!--  Trailing slash has to be avoided on asset paths. Otherwise, <base> does not work  -->
    <base href="<?= $basePath ?>/"/>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="assets/favicon.ico" type="image/x-icon"/>

    <?php
    // Define layout assets
    $layoutCss = [
        'assets/general/general-css/general.css',
        'assets/general/general-css/default.css',
        'assets/general/general-css/layout.css',
        'assets/navbar/side-navbar.css',
        'assets/general/page-component/flash-message/flash-message.css',
    ];
    $layoutJs = [
        'assets/navbar/navbar.js',
    ];
    $layoutJsModules = [
        'assets/general/general-js/default.js',
    ];

    // fetch() includes another template into the current template
    // Include template which contains HTML to include assets
    echo $this->fetch(
        'layout/assets.html.php', // Merge layout assets and from sub templates
        [
            'stylesheets' => array_merge($layoutCss, $css ?? []),
            'scripts' => array_merge($layoutJs, $js ?? []),
            // The type="module" allows the use of import and export inside a JS file.
            'jsModules' => array_merge($layoutJsModules, $jsModules ?? []),
        ]
    );
    ?>

    <title><?= $title ?></title>
    <script>
        // Add the theme immediately to the <html> element before everything is done loading
        const theme = localStorage.getItem('theme') ? localStorage.getItem('theme') : null;
        // Get the theme provided from the server via query param (only after login)
        const themeParam = new URLSearchParams(window.location.search).get('theme');
        // Finally add the theme to the <html> element
        document.documentElement.setAttribute('data-theme', themeParam ?? theme ?? 'light');
        // If a theme from the database is provided and not the same with localStorage, replace it
        if (themeParam && themeParam !== theme) {
            localStorage.setItem('theme', themeParam);
        }
    </script>
</head>
<body>
<!-- "In terms of semantics, <div> is the best choice" as wrapper https://css-tricks.com/best-way-implement-wrapper-css -->
<!-- Wrapper should englobe entire body content as its height is 100vh -->
<div id="wrapper">
    <header>
        <!-- Application name -->
        <span>Slim Example Project</span>
    </header>
    <?= $this->fetch('layout/flash-messages.html.php') ?>

    <!-- Navbar -->
    <aside id="nav-container">
        <nav>
            <a href="<?= $route->urlFor('home-page') ?>"
                <?= $currRouteName === 'home-page' ? 'class="is-active"' : '' ?>>
                <img src="assets/navbar/img/gallery-tiles.svg" alt="Dashboard">
                <img src="assets/navbar/img/gallery-tiles-half-filled.svg" alt="Dashboard">
                <span class="nav-span">Dashboard</span>
            </a>
            <a href="<?= $route->urlFor('client-list-page') ?>"
                <?= in_array($currRouteName, ['client-list-page', 'client-read-page'], true) ?
                    'class="is-active"' : '' ?>>
                <img src="assets/navbar/img/people.svg" alt="Non-assigned">
                <img src="assets/navbar/img/people-filled.svg" alt="People">
                <span class="nav-span">Clients</span>
            </a>
            <a href="<?= $route->urlFor('profile-page') ?>"
                <?= $currRouteName === 'profile-page' ? 'class="is-active"' : '' ?>>
                <img src="assets/navbar/img/user-icon.svg" alt="Profile">
                <img src="assets/navbar/img/user-icon-filled.svg" alt="Profile">
                <span class="nav-span">Profile</span>
            </a>
            <?php
            if ($userListAuthorization === true) { ?>
                <a href="<?= $route->urlFor('user-list-page') ?>"
                    <?= in_array($currRouteName, ['user-list-page', 'user-read-page']) ? 'class="is-active"' : '' ?>>
                    <img src="assets/navbar/img/users.svg" alt="Users">
                    <img src="assets/navbar/img/users-filled.svg" alt="Users">
                    <span class="nav-span">Users</span>
                </a>
                <?php
            } ?>
            <a href="<?= $route->urlFor('logout') ?>"
                <?= $currRouteName === 'logout' ? 'class="is-active"' : '' ?>>
                <img src="assets/navbar/img/logout.svg" alt="Logout">
                <img src="assets/navbar/img/logout-filled.svg" alt="Logout">
                <span class="nav-span">Logout</span>
            </a>
        </nav>
        <div id="nav-mobile-toggle-icon">
            <div id="nav-burger-icon">
                <span></span>
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </aside>
    <main>
        <?= $content ?>
        <?= $this->fetch('layout/request-throttle.html.php') ?>
    </main>

    <footer>
        <address>Made with <img src="assets/general/general-img/heart-icon.svg" alt="heart icon" class="footer-icon"> by
            <a href="https://samuel-gfeller.ch" class="no-style-a" target="_blank" rel="noopener">
                Samuel Gfeller <img src="assets/general/general-img/github-icon.svg" alt="github icon" id="github-icon"
                                    class="footer-icon"></a></address>
    </footer>

</div>

</body>
</html>

