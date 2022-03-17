<?php
/**
 * @var \Psr\Http\Message\UriInterface $uri
 * @var \Slim\Interfaces\RouteParserInterface $route
 * @var array $queryParams containing token, user, token id and possibly other values like redirect
 * @var \App\Domain\User\Data\UserData $user object
 * @var array $config public configuration values
 */
?>
Hello <?= $user->getFullName() ?> <br>
<br>
If you just tried to log in, please take note that your account is suspended. <br>
Please <b><a href="mailto:<?= $config['email']['main_contact_address'] ?>">contact us</a></b> if you wish to activate
your account.
<br>
Best regards <br>
slim-example-project
