<?php
/**
 * @var \Psr\Http\Message\UriInterface $uri
 * @var \Slim\Interfaces\RouteParserInterface $route
 * @var array $queryParams containing token, user, token id and possibly other values like redirect
 * @var string $userFullName
 * @var array $config public configuration values
 */
?>
Guten Tag <?= $userFullName ?> <br>
<br>
Ihr Konto wurde erstellt. <br> <br>
Um zu überprüfen, ob diese E-Mail-Adresse Ihnen gehört, klicken Sie bitte auf den folgenden Link: <br>
<b><a href="<?= $route->fullUrlFor($uri, 'register-verification', [], $queryParams) ?>">Konto verifizieren</a>
</b>.
<br><br>
Hinweis: Dieser Link wird in 2 Stunden ablaufen. Um einen neuen Link zu erhalten, versuchen Sie sich einzuloggen.<br>
<br>
Freundliche Grüsse<br><br>
<?= $config['email']['main_sender_name'] ?>
