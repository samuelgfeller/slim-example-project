<?php

$this->setLayout('layout.html.php');
/**
 * @var \Odan\Session\FlashInterface $flash
 * @var \Slim\Interfaces\RouteParserInterface $route
 * @var array $queryParams query params that should be added to form submit (e.g. redirect)
 * @var null|array $validation validation errors and messages (may be undefined, MUST USE NULL COALESCING)
 * @var string $token verification token
 * @var string $id verification id
 */
?>

<?php
// Define assets that should be included
$this->addAttribute('js', ['assets/auth/password-strength-checker.js']);
$this->addAttribute('css', ['assets/general/css/form.css']); ?>

<h2>Password reset</h2>

<!-- If error flash array is not empty, error class is added to div -->
<div class="form-box <?= isset($formError) ? ' wrong-cred-input' : '' ?>">
    <form action="<?= $route->urlFor('password-reset-submit') ?>"
          class="form" method="post" autocomplete="on">
        <?php
        // Display form error message if there is one
        if (isset($formErrorMessage)) { ?>
            <strong class="err-msg"><?= $formErrorMessage ?></strong>
            <?php
        } ?>

        <!--   Password 1    -->
        <label for="password1-inp">Password</label>
        <input type="password" name="password" id="password1-inp" minlength="3" required
               class="<?= //If there is an error on a specific field, echo error class
               ($passwordErr = get_field_error(($validation ?? []), 'password')) ? 'wrong-cred-input' : '' ?>"
        >
        <?= isset($passwordErr) ? '<strong class="err-msg">' . $passwordErr . '</strong>' : '' ?>

        <!--   Password 2     -->
        <label for="password2-inp">Repeat password</label>
        <input type="password" name="password2" id="password2-inp" minlength="3" required
               class="<?= //If there is an error on a specific field, echo error class
               ($password2Err = get_field_error(($validation ?? []), 'password2')) ? 'wrong-cred-input' : '' ?>
               <?= // If there is error with both passwords
               ($passwordsErr = get_field_error(($validation ?? []), 'passwords')) ? 'wrong-cred-input' : '' ?>"
        >
        <?= isset($password2Err) ? '<strong class="err-msg">' . $password2Err . '</strong>' : '' ?>
        <?= isset($passwordsErr) ? '<strong class="err-msg">' . $passwordsErr . '</strong>' : '' ?>

        <input type="hidden" name="token" value="<?= $token ?>">
        <input type="hidden" name="id" value="<?= $id ?>">

        <input type="submit" id="password-reset-submit-btn" class="submit-btn" value="Set new password">
    </form>
    <br>Or do you want to navigate to the
    <a href="<?= $route->urlFor('login-page', [], $queryParams ?? []) ?>">login page</a>?
</div>

<?php
// Throttle error message in request-throttle.html.php ?>

