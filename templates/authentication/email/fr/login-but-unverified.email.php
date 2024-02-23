<?php
/**
 * @var \Psr\Http\Message\UriInterface $uri
 * @var \Slim\Interfaces\RouteParserInterface $route
 * @var array $queryParams containing token, user, token id and possibly other values like redirect
 * @var string $userFullName
 * @var array $config public configuration values
 */

?>
Bonjour <?= html($userFullName) ?> <br>
<br>
Si vous venez d'essayer de vous connecter, veuillez noter que vous devez d'abord valider votre adresse e-mail. <br>
<br>
Pour vérifier que cette adresse e-mail vous appartient, veuillez cliquer sur le lien suivant : <br>
<b><a href="<?= $route->fullUrlFor($uri, 'register-verification', [], $queryParams) ?>">Vérifier le compte</a>
</b>.
<br><br>
Ce lien expirera dans 2 heures.
<br>
<br>
Meilleures salutations <br><br>
<?= html($config['email']['main_sender_name']) ?>
