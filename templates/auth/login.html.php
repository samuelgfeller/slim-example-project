<?php

$this->setLayout('layout.html.php');
/**
 * @var \Odan\Session\FlashInterface $flash
 * @var \Slim\Interfaces\RouteParserInterface $route
 */
?>

<!-- Define assets that should be included -->
<?php
$this->addAttribute('css', ['assets/general/css/form.css']); ?>

<!--    Display all users an user     -->
<div class="verticalCenter">
    <h2 style="display:inline-block;">Login</h2>
</div>

<!-- If error flash array is not empty, error class is added to div -->
<div class="form-box <?= $flash->get('error') !== [] ? 'wrong-cred-input' : '' ?>" id="login-form-box">
    <form action="<?= $route->urlFor('login-submit') ?>"
          id="login-form" class="form" method="post" autocomplete="on">
        <label for="loginEmailInp">Email</label>
        <input type="email" name="email" id="loginEmailInp"
               placeholder="your@email.com"
               maxlength="254"
               required>
        <label for="loginPasswordInp">Password</label>
        <input type="password" name="password" id="loginPasswordInp"
               minlength="3" required>
        <!--                <br><a class="discrete-link" href="login/password/reset/mail">Lost password?</a>-->
        <!-- reCaptcha -->
        <div class="g-recaptcha" id="recaptcha" data-sitekey="6LcctKoaAAAAAAcqzzgz-19OULxNxtwNPPS35DOU"></div>

        <input type="submit" class="submit-btn" id="submitBtnLogin" value="Login">
    </form>
    <br>Not registered? <a href="register">Register</a>
</div>