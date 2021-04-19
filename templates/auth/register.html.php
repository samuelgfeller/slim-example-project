<?php

/**@var \Odan\Session\FlashInterface $flash
 * @var \Slim\Interfaces\RouteParserInterface $route
 * @var \Slim\Views\PhpRenderer $this
 */

$this->setLayout('layout.html.php');
?>

<!-- Define assets that should be included -->
<?php
$this->addAttribute('css', ['assets/general/css/form.css']);
$this->addAttribute('js', ['assets/auth/auth.js']);
?>
<div class="verticalCenter">
    <h2 style="display:inline-block;">Register</h2>
</div>

<div class="form-box<?= isset($formError) ? ' wrong-cred-input' : '' ?>" id="register-form-box">
    <form class="form" autocomplete="on" id="<?= $route->urlFor('register-submit') ?>" method="post">
        <label for="register-name-inp">Name</label>
        <input type="text" name="name" id="register-name-inp" placeholder="John Doe"
               maxlength="200" minlength="1" autofocus required value="<?= $preloadValues['name'] ?? '' ?>"
            <?php
            // If name validation failed (ternary not possible here as it doesn't allow to assign var in condition)
            if (isset($validation) && $nameErr = get_field_error($validation, 'name')) {
                echo 'class = "wrong-cred-input"';
            } ?>>
        <?= isset($nameErr) ? '<strong class="err-msg">' . $nameErr . '</strong>' : '' ?>

        <label for="register-email-inp">Email</label>
        <input type="email" name="email" id="register-email-inp"
               placeholder="your@email.com"
               maxlength="254"
               required value="<?= $preloadValues['email'] ?? '' ?>"
            <?php
            // If email validation failed (ternary not possible here as it doesn't allow to assign var in condition)
            if (isset($validation) && $emailErr = get_field_error($validation, 'email')) {
                echo 'class = "wrong-cred-input"';
            } ?>>
        <?= isset($emailErr) ? '<strong class="err-msg">' . $emailErr . '</strong>' : '' ?>
        <label for="register-password1-inp">Password</label>
        <input type="password" name="password" id="register-password1-inp" minlength="3" required
            <?php
            // If password validation failed
            if (isset($validation) && $passwordErr = get_field_error($validation, 'password')) {
                echo 'class = "wrong-cred-input"';
            } ?>>
        <?= isset($passwordErr) ? '<strong class="err-msg">' . $passwordErr . '</strong>' : '' ?>
        <label for="register-password2-inp">Repeat password</label>
        <input type="password" name="password2" id="register-password2-inp" minlength="3" required
            <?php
            // If password2 validation failed
            if (isset($validation) && $password2Err = get_field_error($validation, 'password2')) {
                echo 'class = "wrong-cred-input"';
            }
            // If there is error with both passwords (has to be in a separate if as it wont
            // continue the condition and execute any function after first OR is truthy)
            if (isset($validation) && $passwordsErr = get_field_error($validation, 'passwords')) {
                echo 'class = "wrong-cred-input"';
            } ?>>
        <?= isset($password2Err) ? '<strong class="err-msg">' . $password2Err . '</strong>' : '' ?>
        <?= isset($passwordsErr) ? '<strong class="err-msg">' . $passwordsErr . '</strong>' : '' ?>

        <!-- reCaptcha -->
        <div class="g-recaptcha" id="recaptcha" data-sitekey="6LcctKoaAAAAAAcqzzgz-19OULxNxtwNPPS35DOU"></div>

        <input type="submit" class="submit-btn" id="register-submit-btn" value="Create account">

    </form>
    <br>Do you already have an account? <a href="<?= $route->urlFor('login-page') ?>">Login</a>
</div>
