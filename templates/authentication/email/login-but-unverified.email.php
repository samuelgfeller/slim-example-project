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
If you just tried to log in, please take note that you have to validate your email address first. <br>
This means clicking on the link provided in the email that you should have received after registration.
Make sure to check in the spam folder.

<!-- Following sentence asserted in UserRegistererTest.php and RegisterSubmitActionTest.php -->
You have the opportunity to verify that this email address belongs to you here by clicking on the following link: <br>
<b><a href="<?= $route->fullUrlFor($uri, 'register-verification', [], $queryParams) ?>">Verify account</a></b>.
<br>
Note: this link will expire in 2 hours. To receive a new one you can
<a href="<?= $route->fullUrlFor($uri, 'register-page') ?>">register</a> once again.<br>
<br>
Best regards <br>
slim-example-project
