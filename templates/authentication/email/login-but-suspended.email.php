<?php
/**
 * @var \Psr\Http\Message\UriInterface $uri
 * @var \Slim\Interfaces\RouteParserInterface $route
 * @var array $queryParams containing token, user, token id and possibly other values like redirect
 * @var string $userFullName
 * @var array $config public configuration values
 */
?>
Hello <?= $userFullName ?> <br>
<br>
<?php /** Following sentence asserted at @see LoginSubmitActionTest */?>
If you just tried to log in, please take note that your account is suspended. <br>
Please <b><a href="mailto:<?= $config['email']['main_contact_address'] ?>">contact us</a></b> if you wish to activate
your account. <br>
<br>
Kind regards <br><br>
<?= $config['email']['main_sender_name'] ?>
