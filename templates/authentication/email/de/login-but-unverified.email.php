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
Wenn Sie gerade versucht haben, sich anzumelden, beachten Sie bitte, dass Sie zunächst Ihre E-Mail-Adresse
bestätigen müssen. <br>
<br>
Um zu überprüfen, dass diese E-Mail-Adresse Ihnen gehört, klicken Sie bitte auf den folgenden Link:
<b><a href="<?= $route->fullUrlFor($uri, 'register-verification', [], $queryParams) ?>">Konto verifizieren</a>
</b>.
<br><br>
Dieser Link wird in 2 Stunden ablaufen.<br>
<br>
Freundliche Grüsse<br><br>
<?= $config['email']['main_sender_name'] ?>