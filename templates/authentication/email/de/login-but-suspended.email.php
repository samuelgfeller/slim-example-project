<?php
/**
 * @var \Psr\Http\Message\UriInterface $uri
 * @var \Slim\Interfaces\RouteParserInterface $route
 * @var array $queryParams containing token, user, token id and possibly other values like redirect
 * @var \App\Domain\User\Data\UserData $user object
 * @var array $config public configuration values
 */
?>
Guten Tag <?= $user->getFullName() ?> <br>
<br>
Wenn Sie gerade versucht haben, sich einzuloggen, beachten Sie bitte, dass Ihr Konto gesperrt ist. <br>
Bitte <b><a href="mailto:<?= $config['email']['main_contact_address'] ?>">kontaktieren Sie uns</a></b> wenn Sie Ihr Konto
aktivieren möchten.

<br>
Freundliche Grüsse <br><br>
<?= $config['email']['main_sender_name'] ?>
