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

<!-- Following sentence asserted in UserRegistererTest.php and RegisterSubmitActionTest.php -->
You have the opportunity here as well to verify that this email address belongs by clicking on the following link:
<b><a href="<?= $route->fullUrlFor($uri, 'register-verification', [], $queryParams) ?>">Verify account</a></b>.
<br>
This link will expire in 2 hours. To receive a new one you can
<a href="<?= $route->fullUrlFor($uri, 'register-page') ?>">register</a> once again.<br>
<br>
If however you did NOT log in, your credentials may not be safe, and I urge you to change password after verification
in the <a href="<?= $route->fullUrlFor($uri, 'profile-page') ?>">profile</a> section.
<br>
Best regards <br>
slim-example-project