<?php

/**@var \Odan\Session\FlashInterface $flash
 * @var \Slim\Interfaces\RouteParserInterface $route
 * @var \Slim\Views\PhpRenderer $this
 * @var array $queryParams query params that should be added to form submit (e.g. redirect)
 * @var null|array $validation validation errors and messages (may be undefined, MUST USE NULL COALESCING)
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
    <form class="form" autocomplete="on"
          id="<?= $route->urlFor('register-submit', [], $queryParams ?? []) ?>" method="post">
        <label for="register-name-inp">Name</label>
        <input type="text" name="name" id="register-name-inp" placeholder="John Doe"
               maxlength="200" minlength="1" autofocus required value="<?= $preloadValues['name'] ?? '' ?>"
               class="<?= //If there is an error on a specific field, echo error class
        ($nameErr = get_field_error(($validation ?? []), 'name')) ? 'wrong-cred-input' : '' ?>"
        >
        <?= $nameErr !== null ? '<strong class="err-msg">' . $nameErr . '</strong>' : '' ?>
        <label for="register-email-inp">Email</label>
        <input type="email" name="email" id="register-email-inp"
               placeholder="your@email.com"
               maxlength="254"
               required value="<?= $preloadValues['email'] ?? '' ?>"
               class="<?= //If there is an error on a specific field, echo error class
               ($emailErr = get_field_error(($validation ?? []), 'email')) ? 'wrong-cred-input' : '' ?>"
        >
        <?= isset($emailErr) ? '<strong class="err-msg">' . $emailErr . '</strong>' : '' ?>
        <label for="register-password1-inp">Password</label>
        <input type="password" name="password" id="register-password1-inp" minlength="3" required
               class="<?= //If there is an error on a specific field, echo error class
               ($passwordErr = get_field_error(($validation ?? []), 'password')) ? 'wrong-cred-input' : '' ?>"
        >
        <?= isset($passwordErr) ? '<strong class="err-msg">' . $passwordErr . '</strong>' : '' ?>
        <label for="register-password2-inp">Repeat password</label>
        <input type="password" name="password2" id="register-password2-inp" minlength="3" required
               class="<?= //If there is an error on a specific field, echo error class
               ($password2Err = get_field_error(($validation ?? []), 'password2')) ? 'wrong-cred-input' : '' ?>
               <?= // If there is error with both passwords
               ($passwordsErr = get_field_error(($validation ?? []), 'passwords')) ? 'wrong-cred-input' : '' ?>"
        >
        <?= isset($password2Err) ? '<strong class="err-msg">' . $password2Err . '</strong>' : '' ?>
        <?= isset($passwordsErr) ? '<strong class="err-msg">' . $passwordsErr . '</strong>' : '' ?>

        <!-- reCaptcha -->
        <div class="g-recaptcha" id="recaptcha" data-sitekey="6LcctKoaAAAAAAcqzzgz-19OULxNxtwNPPS35DOU"></div>

        <input type="submit" class="submit-btn" id="register-submit-btn" value="Create account">

    </form>
    <br>Do you already have an account?
    <a href="<?= $route->urlFor('login-page', [], $queryParams ?? []) ?>">Login</a>
</div>
