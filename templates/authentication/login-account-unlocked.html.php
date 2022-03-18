<?php

/**
 * @var \Psr\Http\Message\UriInterface $uri
 * @var \Slim\Interfaces\RouteParserInterface $route
 * @var \Slim\Views\PhpRenderer $this
 * @var array $queryParams query params that should be added to form submit (e.g. redirect)
 * @var null|array $validation validation errors and messages (may be undefined, MUST USE NULL COALESCING)
 */

$this->setLayout('layout.html.php');
?>

<h2 style="display:inline-block;">Congratulation! Your account has been unlocked.</h2>
<h3>You are now logged in.</h3>
<p>
    In case you don't know your password anymore, don't hesitate to change your password
    in the <a href="<?= $route->fullUrlFor($uri, 'profile-page') ?>">profile</a> section.
</p>
<p>Go to the
<a href="<?= $route->fullUrlFor($uri, 'home-page') ?>">home page</a>.
</p>


