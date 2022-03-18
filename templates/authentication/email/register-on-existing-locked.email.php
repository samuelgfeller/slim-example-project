<?php
/**
 * Email to be send if a user tries to register with an email that already exists and the existing user is suspended
 * @var Slim\Views\PhpRenderer $this
 * @var Psr\Http\Message\UriInterface $uri
 * @var Slim\Interfaces\RouteParserInterface $route
 * @var App\Domain\User\Data\UserData $user already existing registered user (result of findUserByEmail())
 * @var array $config public configuration values
 */

$this->setLayout('layout/layout.email.php');
?>

<p>
    Hello <?= $user->getFullName() ?><br>
    <br>
    Someone tried to create an account with your email address. <br>
    <!-- Sentence defined for assertion in RegisterCaseProvider.php    -->
    If this was you, then we have the regret to inform you that your account is locked for security reasons. <br>
    Please <a href="mailto:<?= $config['email']['main_contact_address'] ?>">contact us</a> if you wish to activate
    your account.
    <br><br>
    Best regards <br><br>
    Slim Example Project
</p>


