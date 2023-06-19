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
Your account has been created. <br>
<br>
Pour vérifier que cette adresse e-mail vous appartient, veuillez cliquer sur le lien suivant :<br>
<b><a href="<?= $route->fullUrlFor($uri, 'register-verification', [], $queryParams) ?>">Vérifier le compte</a>
</b>.
<br><br>
Ce lien expirera dans 2 heures. Pour recevoir un nouveau lien, essayez de vous connecter.<br>
<br>
Meilleures salutations <br><br>
<?= $config['email']['main_sender_name'] ?>
