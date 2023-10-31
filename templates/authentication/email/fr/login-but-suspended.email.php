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
Si vous venez d'essayer de vous connecter, veuillez noter que votre compte est suspendu. <br>
Veuillez <b><a href="mailto:<?= $config['email']['main_contact_address'] ?>">nous contacter</a></b> si vous souhaitez
activer votre compte.
<br>
Meilleures salutations <br><br>
<?= $config['email']['main_sender_name'] ?>