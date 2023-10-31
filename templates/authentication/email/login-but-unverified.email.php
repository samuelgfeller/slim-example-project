<?php
/**
 * @var \Psr\Http\Message\UriInterface $uri
 * @var \Slim\Interfaces\RouteParserInterface $route
 * @var array $queryParams containing token, user, token id and possibly other values like redirect
 * @var string $userFullName
 * @var array $config public configuration values
 */
?>
Hello <?= $userFullName ?> <br>
<br>
<?php /** Following sentence asserted at @see LoginSubmitActionTest */?>
If you just tried to log in, please take note that you have to validate your email address first. <br>
<br>
To verify that this email address belongs to you, please click on the following link: <br>
<b><a href="<?= $route->fullUrlFor($uri, 'register-verification', [], $queryParams) ?>">Verify account</a></b>.
<br><br>
This link will expire in 2 hours.
<br><br>
Kind regards <br><br>
<?= $config['email']['main_sender_name'] ?>
