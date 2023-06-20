<?php
/**
 * @var \Psr\Http\Message\UriInterface $uri
 * @var \Slim\Interfaces\RouteParserInterface $route
 * @var array $queryParams containing token, user, token id and possibly other values like redirect
 * @var \App\Domain\User\Data\UserData $user object
 * @var array $config public configuration values
 */

?>
Guten Tag <?= $user->getFullName() ?> <br>
<br>
Wenn Sie gerade versucht haben, sich einzuloggen, beachten Sie bitte, dass Ihr Konto gesperrt ist. <br>
Dies kann bedeuten, dass jemand wiederholt versucht hat, sich mit einem falschen Passwort bei Ihrem Konto anzumelden.

Sie können Ihr Konto freischalten, indem Sie auf den folgenden Link klicken:
<b><a href="<?= $route->fullUrlFor($uri, 'account-unlock-verification', [], $queryParams) ?>">
        Konto verifizieren</a></b>
<br><br>

Der Link wird in 2 Stunden ablaufen. <br>
<br>
Freundliche Grüsse<br><br>
<?= $config['email']['main_sender_name'] ?>