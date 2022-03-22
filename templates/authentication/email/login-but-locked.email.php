<?php
/**
 * @var \Psr\Http\Message\UriInterface $uri
 * @var \Slim\Interfaces\RouteParserInterface $route
 * @var array $queryParams containing token, user, token id and possibly other values like redirect
 * @var \App\Domain\User\Data\UserData $user object
 */
?>
Hello <?= $user->getFullName() ?> <br>
<br>
<?php /** Following sentence asserted at @see LoginSubmitActionTest */?>
If you just tried to log in, please take note that your account is locked. <br>
This may mean that someone tried to log into your account repeatedly with an incorrect password.

You can unlock your account by clicking on the following link:
<b><a href="<?= $route->fullUrlFor($uri, 'account-unlock-verification', [], $queryParams) ?>">verify account</a></b>.
<br><br>

The link will expire in 2 hours. <br>
<br>
Best regards <br><br>
slim-example-project