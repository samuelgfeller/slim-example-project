<?php
/**
 * @var \Psr\Http\Message\UriInterface $uri
 * @var \Slim\Interfaces\RouteParserInterface $route
 * @var array $queryParams containing token, user, token id and possibly other values like redirect
 * @var \App\Module\User\Data\UserData $user
 * @var array $config public configuration values
 */
?>
Guten Tag <?= html($user->getFullName()) ?> <br>
<br>
Wenn Sie vor kurzem Ihr Passwort zurückgesetzt haben, klicken Sie auf den unten stehenden Link, um dies zu tun. <br>
<br>
<b><a href="<?= $route->fullUrlFor($uri, 'password-reset-submit', [], $queryParams) ?>">Neues Passwort erstellen</a></b>.
<br><br>

Der Link wird in 2 Stunden ablaufen. <br>
<br>
Freundliche Grüsse <br><br>
<?= html($config['email']['main_sender_name']) ?>