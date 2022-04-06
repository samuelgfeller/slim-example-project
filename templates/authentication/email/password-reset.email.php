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
<?php /** Following sentence asserted @see \App\Test\Integration\User\PasswordForgottenEmailSubmitActionTest */?>
If you recently requested to reset your password, click the link below to do so. <br>
<br>
<b><a href="<?= $route->fullUrlFor($uri, 'password-reset-submit', [], $queryParams) ?>">Create new password</a></b>.
<br><br>

The link will expire in 2 hours. <br>
<br>
Best regards <br><br>
slim-example-project