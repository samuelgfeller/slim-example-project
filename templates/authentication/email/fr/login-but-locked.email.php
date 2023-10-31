<?php
/**
 * @var \Psr\Http\Message\UriInterface $uri
 * @var \Slim\Interfaces\RouteParserInterface $route
 * @var array $queryParams containing token, user, token id and possibly other values like redirect
 * @var string $userFullName
 * @var array $config public configuration values
 */

?>
Bonjour <?= $userFullName ?> <br>
<br>
Si vous venez d'essayer de vous connecter, veuillez noter que votre compte est bloqué. <br>
Cela peut signifier que quelqu'un a essayé de se connecter à votre compte à plusieurs reprises avec un mot de passe
incorrect. <br>
<br>
Vous pouvez débloquer votre compte en cliquant sur le lien suivant :
<b><a href="<?= $route->fullUrlFor($uri, 'account-unlock-verification', [], $queryParams) ?>">
        vérifier le compte</a></b>.
<br><br>

Ce lien expirera dans 2 heures. <br>
<br>
Meilleures salutations <br><br>
<?= $config['email']['main_sender_name'] ?>
