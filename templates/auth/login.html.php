<?php

$this->setLayout('layout.html.php');
/**
 * @var \Odan\Session\FlashInterface $flash
 * @var \Slim\Interfaces\RouteParserInterface $route
 * @var array $queryParams query params that should be added to form submit (e.g. redirect)
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
            <?php
            // If name validation failed (ternary not possible here as it doesn't allow to assign var in condition)
            if (isset($validation) && $nameErr = get_field_error($validation, 'email')) {
                echo 'class = "wrong-cred-input"';
            } ?>>
        <?= isset($nameErr) ? '<strong class="err-msg">' . $nameErr . '</strong>' : '' ?>

        <label for="loginPasswordInp">Password</label>
        <input type="password" name="password" id="loginPasswordInp" minlength="3" required
            <?php
            // If password validation failed
            if (isset($validation) && $passwordErr = get_field_error($validation, 'password')) {
                echo 'class = "wrong-cred-input"';
            } ?>>
        <?= isset($passwordErr) ? '<strong class="err-msg">' . $passwordErr . '</strong>' : '' ?>
        <!--                <br><a class="discrete-link" href="login/password/reset/mail">Lost password?</a>-->
        <!-- reCaptcha -->
        <div class="g-recaptcha" id="recaptcha" data-sitekey="6LcctKoaAAAAAAcqzzgz-19OULxNxtwNPPS35DOU"></div>

        <input type="submit" class="submit-btn" id="submitBtnLogin" value="Login">
    </form>
    <br>Not registered?
    <a href="<?= $route->urlFor('register-page', [], $queryParams ?? []) ?>">Register</a>

</div>