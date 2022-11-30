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
$this->addAttribute('js', ['assets/general/js/form-input-name-replacer.js', 'assets/auth/password-strength-checker.js']);
?>

<h2>Register</h2>
<div class="form-background">
    <div class="form-container<?= isset($formError) ? ' invalid-input' : '' ?>" id="register-form-container">
        <form class="form" autocomplete="off"
              id="<?= $route->urlFor('register-submit', [], $queryParams ?? []) ?>" method="post">

            <?php // Display form error message if there is one
            if (isset($formErrorMessage)) { ?>
                <strong id="form-general-error-msg" class="error-panel"><?= $formErrorMessage ?></strong>
                <?php
            } ?>

            <!--   First name     -->
            <div class="form-input-group <?= //If there is an error on a specific field, echo error class
            ($firstNameErr = get_field_error(($validation ?? []), 'first_name')) ? ' input-group-error' : '' ?>">
                <input type="text" name="first_name"
                       maxlength="100" minlength="3" required value="<?= $preloadValues['firstName'] ?? '' ?>">
                <label>First name</label>
                <?= // Error message below input field
                $firstNameErr !== null ? '<strong class="err-msg">' . $firstNameErr . '</strong>' : '' ?>
            </div>

            <!--   Surname     -->
            <div class="form-input-group <?= //If there is an error on a specific field, echo error class
            ($surnameErr = get_field_error(($validation ?? []), 'surname')) ? ' input-group-error' : '' ?>">
                <input type="text" name="surname"
                       maxlength="100" minlength="3" required value="<?= $preloadValues['surname'] ?? '' ?>">
                <label>Surname</label>
                <?= $surnameErr !== null ? '<strong class="err-msg">' . $surnameErr . '</strong>' : '' ?>
            </div>

            <!--    Email    -->
            <div class="form-input-group <?= //If there is an error on a specific field, echo error class
            ($emailErr = get_field_error(($validation ?? []), 'email')) ? ' input-group-error' : '' ?>">
                <input type="email" name="email"
                       maxlength="254"
                       required value="<?= $preloadValues['email'] ?? '' ?>">
                <label>Email</label>
                <?= isset($emailErr) ? '<strong class="err-msg">' . $emailErr . '</strong>' : '' ?>
            </div>

            <!--   Password 1    -->
            <div id="password1-input-group" class="form-input-group <?= ($passwordErr = get_field_error(
                ($validation ?? []),
                'password'
            )) ? ' input-group-error' : '' ?>">
                <input type="password" name="password" minlength="3" id="password1-input" required>
                <label>Password</label>
                <?= isset($passwordErr) ? '<strong class="err-msg">' . $passwordErr . '</strong>' : '' ?>
            </div>

            <!--   Password 2     -->
            <div class="form-input-group <?= //If there is an error on a specific field, echo error class
            ($password2Err = get_field_error(($validation ?? []), 'password2')) ? ' input-group-error' : '' ?>">
                <input type="password" name="password2" id="password2-input" minlength="3" required>
                <label>Repeat password</label>
                <?= isset($password2Err) ? '<strong class="err-msg">' . $password2Err . '</strong>' : '' ?>
            </div>
            <?= // If there is error with both passwords
            ($passwordsErr = get_field_error(($validation ?? []), 'passwords')) ? '<strong class="err-msg">' .
                $passwordsErr . '</strong><br>' : '' ?>

            <!-- reCaptcha -->
            <div class="g-recaptcha" id="recaptcha" data-sitekey="6LcctKoaAAAAAAcqzzgz-19OULxNxtwNPPS35DOU"></div>

            <!--   Submit    -->
            <input type="submit" class="submit-btn" id="register-submit-btn" value="Create account">


        </form>
        <span class="discrete-link">
        <br>Do you already have an account?
        <a href="<?= $route->urlFor('login-page', [], $queryParams ?? []) ?>">Login</a>
        </span>

    </div>
</div>
