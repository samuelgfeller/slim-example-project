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
Your account has been created. <br>
<!-- Following sentence asserted in UserCreatorTest.php and RegisterSubmitActionTest.php -->
To verify that this email address belongs to you, please click on the following link: <br>
<b><a href="<?= $route->fullUrlFor($uri, 'register-verification', [], $queryParams) ?>">Verify account</a></b>.
<br><br>
Note: this link will expire in 2 hours. To receive a new one please contact the account issuer.<br>
<br>
Best regards <br><br>
slim-example-project
