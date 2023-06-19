<?php
/**
 * @var \Psr\Http\Message\UriInterface $uri
 * @var \Slim\Interfaces\RouteParserInterface $route
 * @var array $queryParams containing token, user, token id and possibly other values like redirect
 * @var \App\Domain\User\Data\UserData $user object
 * @var array $config public configuration values
 */
?>
Hello <?= $user->getFullName() ?> <br>
<br>
Your account has been created. <br> <br>
To verify that this email address belongs to you, please click on the following link: <br>
<b><a href="<?= $route->fullUrlFor($uri, 'register-verification', [], $queryParams) ?>">Verify account</a></b>.
<br><br>
Note: this link will expire in 2 hours. To get a new link, try logging in.<br>
<br><br>
Kind regards <br><br>
<?= $config['email']['main_sender_name'] ?>
