<?php
/**
 * @var \Psr\Http\Message\UriInterface $uri
 * @var \Slim\Interfaces\RouteParserInterface $route
 * @var array $queryParams containing token, user, token id and possibly other values like redirect
 * @var \App\Domain\User\DTO\User $user object
 */

?>
Hello <?= $user->name ?> <br>
<br>
To verify that this email address belongs to you, please click on the following link: <br>
<b><a href="<?= $route->fullUrlFor($uri, 'register-verification', [], $queryParams) ?>"
    >Verify account</a></b>. <br>
Note: this link will expire in 2 hours. <br>
<br>
Best regards <br>
slim-example-project
