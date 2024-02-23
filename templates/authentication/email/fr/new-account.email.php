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
Votre compte a été créé. <br>
<br>
Pour vérifier que cette adresse mail vous appartient, veuillez cliquer sur le lien suivant :<br>
<b><a href="<?= $route->fullUrlFor($uri, 'register-verification', [], $queryParams) ?>">Vérifier le compte</a>
</b>
<br><br>
Ce lien expirera dans 2 heures. Pour recevoir un nouveau lien, essayez de vous connecter.<br>
<br>
Meilleures salutations <br><br>
<?= html($config['email']['main_sender_name']) ?>
