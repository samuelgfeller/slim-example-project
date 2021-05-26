<?php

$this->setLayout('layout.html.php');
/**
 * @var \Odan\Session\FlashInterface $flash
 * @var \Slim\Interfaces\RouteParserInterface $route
 * @var array $queryParams query params that should be added to form submit (e.g. redirect)
 * @var null|array $validation validation errors and messages (may be undefined, MUST USE NULL COALESCING)
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
<div class="form-box <?= isset($formError) ? ' wrong-cred-input' : '' ?>" id="login-form-box">
    <form action="<?= $route->urlFor('login-submit', [], $queryParams ?? []) ?>"
          id="login-form" class="form" method="post" autocomplete="on">
        <label for="loginEmailInp">Email</label>
        <input type="email" name="email" id="loginEmailInp"
               placeholder="your@email.com"
               maxlength="254"
               required value="<?= $preloadValues['email'] ?? '' ?>"
               class="<?= //If there is an error on a specific field, echo error class
               ($emailErr = get_field_error(($validation ?? []), 'email')) ? 'wrong-cred-input' : '' ?>"
        >
        <?= isset($emailErr) ? '<strong class="err-msg">' . $emailErr . '</strong>' : '' ?>

        <label for="loginPasswordInp">Password</label>
        <input type="password" name="password" id="loginPasswordInp" minlength="3" required
               class="<?= //If there is an error on a specific field, echo error class
               ($passwordErr = get_field_error(($validation ?? []), 'password')) ? 'wrong-cred-input' : '' ?>"
        >
        <?= isset($passwordErr) ? '<strong class="err-msg">' . $passwordErr . '</strong>' : '' ?>
        <!--                <br><a class="discrete-link" href="login/password/reset/mail">Lost password?</a>-->
        <!-- reCaptcha -->
        <div class="g-recaptcha" id="recaptcha" data-sitekey="6LcctKoaAAAAAAcqzzgz-19OULxNxtwNPPS35DOU"></div>

        <input type="submit" class="submit-btn" id="submitBtnLogin" value="Login">
    </form>
    <br>Not registered?
    <a href="<?= $route->urlFor('register-page', [], $queryParams ?? []) ?>">Register</a>

</div>