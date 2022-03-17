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
If you just tried to log in, please take note that your account is locked. <br>
This may mean that someone tried to log into your account repeatedly with an incorrect password.

<!-- Following sentence asserted in UserRegistererTest.php and RegisterSubmitActionTest.php -->
You can unlock your account by clicking on the following link:
<b><a href="<?= $route->fullUrlFor($uri, 'login-locked-verification', [], $queryParams) ?>">verify account</a></b>.
<br><br>

<br><br>
The link will expire in 2 hours. <br>
<br>

<br>
Best regards <br>
slim-example-project