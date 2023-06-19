<?php
/**
 * @var \Psr\Http\Message\UriInterface $uri
 * @var \Slim\Interfaces\RouteParserInterface $route
 * @var array $queryParams containing token, user, token id and possibly other values like redirect
 * @var \App\Domain\User\Data\UserData $user object
 * @var array $config public configuration values
 */
?>
Bonjour <?= $user->getFullName() ?> <br>
<br>
Si vous avez récemment demandé à réinitialiser votre mot de passe, cliquez sur le lien ci-dessous. <br>
<br>
<b><a href="<?= $route->fullUrlFor($uri, 'password-reset-submit', [], $queryParams) ?>">
        Créer un nouveau mot de passe</a></b>.
<br><br>

Ce lien expirera dans 2 heures. <br>
<br>
Meilleures salutations <br><br>
<?= $config['email']['main_sender_name'] ?>
