<?php
/**
 * @var \Slim\Views\PhpRenderer $this
 * @var \Slim\Interfaces\RouteParserInterface $route
 * @var string $currRouteName
 * @var bool $userListAuthorization
 */
?>
<aside id="nav-container">
    <nav>
        <a href="<?= $route->urlFor('home-page') ?>"
            <?= $currRouteName === 'home-page' ? 'class="is-active"' : '' ?>>
            <img src="assets/navbar/img/gallery-tiles.svg" alt="Dashboard">
            <img src="assets/navbar/img/gallery-tiles-half-filled.svg" alt="Dashboard">
            <span class="nav-span"><?= __('Dashboard') ?></span>
        </a>
        <a href="<?= $route->urlFor('client-list-page') ?>"
            <?= in_array($currRouteName, ['client-list-page', 'client-read-page'], true) ?
                'class="is-active"' : '' ?>>
            <img src="assets/navbar/img/people.svg" alt="Non-assigned">
            <img src="assets/navbar/img/people-filled.svg" alt="People">
            <span class="nav-span"><?= __('Clients') ?></span>
        </a>
        <a href="<?= $route->urlFor('profile-page') ?>"
            <?= $currRouteName === 'profile-page' ? 'class="is-active"' : '' ?>>
            <img src="assets/navbar/img/user-icon.svg" alt="Profile">
            <img src="assets/navbar/img/user-icon-filled.svg" alt="Profile">
            <span class="nav-span"><?= __('Profile') ?></span>
        </a>
        <?php
        if (isset($userListAuthorization) && $userListAuthorization === true) { ?>
            <a href="<?= $route->urlFor('user-list-page') ?>"
                <?= in_array(
                    $currRouteName,
                    ['user-list-page', 'user-read-page']
                ) ? 'class="is-active"' : '' ?>>
                <img src="assets/navbar/img/users.svg" alt="Users">
                <img src="assets/navbar/img/users-filled.svg" alt="Users">
                <span class="nav-span"><?= __('Users') ?></span>
            </a>
            <?php
        } ?>
        <a href="<?= $route->urlFor('logout') ?>"
            <?= $currRouteName === 'logout' ? 'class="is-active"' : '' ?>>
            <img src="assets/navbar/img/logout.svg" alt="Logout">
            <img src="assets/navbar/img/logout-filled.svg" alt="Logout">
            <span class="nav-span"><?= __('Logout') ?></span>
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