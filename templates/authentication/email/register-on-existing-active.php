<?php
/**
 * Email to be send if a user tries to register with an email that already exists and the existing user is suspended
 * @var Slim\Views\PhpRenderer $this
 * @var Psr\Http\Message\UriInterface $uri
 * @var Slim\Interfaces\RouteParserInterface $route
 * @var App\Domain\User\Data\UserData $user already existing registered user (result of findUserByEmail())
 */

$this->setLayout('layout/layout.email.php');
?>

<p>
    Hello <?= $user->name ?><br>
    <br>
    Someone tried to create an account with your email address. <br>
    <!-- Sentence defined for assertion in RegisterCaseProvider.php    -->
    If this was you, then you can login with your credentials by navigating to the
    <a href="<?= $route->fullUrlFor($uri,'login-page') ?>">login section</a> or if you forgot your
    password, you can reset it here. <br>
    <br><br>
    Best regards <br>
    Slim Example Project
</p>


