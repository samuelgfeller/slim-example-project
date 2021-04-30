<?php
/**
 * @var \Psr\Http\Message\UriInterface $uri
 * @var \Slim\Interfaces\RouteParserInterface $route
 * @var string $token generated token
 * @var string $id token (user_verification table) id
 * @var \App\Domain\User\User $user object
 */

?>
Hello <?= $user->name ?> <br>
<br>
To verify that this email address belongs to you, please click on the following link: <br>
<b><a href="<?= $route->fullUrlFor($uri, 'register-verification') . '?' . http_build_query(
        ['token' => $token, 'id' => $id]
    ) ?>"
    >Verify account</a></b>
