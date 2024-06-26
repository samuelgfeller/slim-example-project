<?php
/**
 * @var \Psr\Http\Message\UriInterface $uri
 * @var \Slim\Interfaces\RouteParserInterface $route
 * @var array $queryParams containing token, user, token id and possibly other values like redirect
 * @var string $userFullName
 * @var array $config public configuration values
 */
?>
Guten Tag <?= html($userFullName) ?> <br>
<br>
Wenn Sie gerade versucht haben, sich einzuloggen, beachten Sie bitte, dass Ihr Konto gesperrt ist. <br>
Bitte <b><a href="mailto:<?= $config['email']['main_contact_email'] ?>">kontaktieren Sie uns</a></b> wenn Sie Ihr Konto
aktivieren möchten.

<br>
Freundliche Grüsse <br><br>
<?= html($config['email']['main_sender_name']) ?>
