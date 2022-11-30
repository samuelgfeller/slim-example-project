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
If you just tried to log in, please take note that you have to validate your email address first. <br>
This means clicking on the link provided in the email that you should have received after registration.

You have the opportunity here as well to verify that this email address belongs to you by clicking on the following link:
<b><a href="<?= $route->fullUrlFor($uri, 'register-verification', [], $queryParams) ?>">Verify account</a></b>.
<br><br>
This link will expire in 2 hours.
<br>
If however you did NOT log in, your credentials are NOT safe, and I urge you to change password after verification
in the <a href="<?= $route->fullUrlFor($uri, 'profile-page') ?>">profile</a> section.
<br><br>
Best regards <br><br>
slim-example-project