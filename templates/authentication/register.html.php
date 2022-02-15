<?php

/**@var \Odan\Session\FlashInterface $flash
 * @var \Slim\Interfaces\RouteParserInterface $route
 * @var \Slim\Views\PhpRenderer $this
 * @var array $queryParams query params that should be added to form submit (e.g. redirect)
 * @var null|array $validation validation errors and messages (may be undefined, MUST USE NULL COALESCING)
 */

$this->setLayout('layout.html.php');
?>

<?php
// Define assets that should be included
$this->addAttribute('css', ['assets/general/css/form.css']);
$this->addAttribute('js', ['assets/auth/auth.js']);
?>
<div class="verticalCenter">
    <h2 style="display:inline-block;">Register</h2>
</div>

<div class="form-box<?= isset($formError) ? ' wrong-cred-input' : '' ?>" id="register-form-box">
    <form class="form" autocomplete="on"
          id="<?= $route->urlFor('register-submit', [], $queryParams ?? []) ?>" method="post">

        <!--   First name     -->
        <label for="register-first-name-inp">First name</label>
        <input type="text" name="first_name" id="register-first-name-inp" placeholder="John"
               maxlength="100" minlength="1" autofocus required value="<?= $preloadValues['first_name'] ?? '' ?>"
               class="<?= //If there is an error on a specific field, echo error class
        ($firstNameErr = get_field_error(($validation ?? []), 'first_name')) ? 'wrong-cred-input' : '' ?>"
        >
        <?= // Error message below input field
        $firstNameErr !== null ? '<strong class="err-msg">' . $firstNameErr . '</strong>' : '' ?>

        <!--   Surname     -->
        <label for="register-surname-inp">Surname</label>
        <input type="text" name="surname" id="register-surname-inp" placeholder="Doe"
               maxlength="100" minlength="1" autofocus required value="<?= $preloadValues['surname'] ?? '' ?>"
               class="<?= //If there is an error on a specific field, echo error class
        ($surnameErr = get_field_error(($validation ?? []), 'surname')) ? 'wrong-cred-input' : '' ?>"
        >
        <?= $surnameErr !== null ? '<strong class="err-msg">' . $surnameErr . '</strong>' : '' ?>

        <!--    Email    -->
        <label for="register-email-inp">Email</label>
        <input type="email" name="email" id="register-email-inp"
               placeholder="your@email.com"
               maxlength="254"
               required value="<?= $preloadValues['email'] ?? '' ?>"
               class="<?= //If there is an error on a specific field, echo error class
               ($emailErr = get_field_error(($validation ?? []), 'email')) ? 'wrong-cred-input' : '' ?>"
        >
        <?= isset($emailErr) ? '<strong class="err-msg">' . $emailErr . '</strong>' : '' ?>

        <!--   Password 1    -->
        <label for="register-password1-inp">Password</label>
        <input type="password" name="password" id="register-password1-inp" minlength="3" required
               class="<?= //If there is an error on a specific field, echo error class
               ($passwordErr = get_field_error(($validation ?? []), 'password')) ? 'wrong-cred-input' : '' ?>"
        >
        <?= isset($passwordErr) ? '<strong class="err-msg">' . $passwordErr . '</strong>' : '' ?>

        <!--   Password 2     -->
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

        <!--   Submit    -->
        <input type="submit" class="submit-btn" id="register-submit-btn" value="Create account">

    </form>
    <br>Do you already have an account?
    <a href="<?= $route->urlFor('login-page', [], $queryParams ?? []) ?>">Login</a>
</div>
