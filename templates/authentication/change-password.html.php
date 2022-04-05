<?php

$this->setLayout('layout.html.php');
/**
 * Change password while authenticated template
 *
 * @var \Odan\Session\FlashInterface $flash
 * @var \Slim\Interfaces\RouteParserInterface $route
 * @var array $queryParams query params that should be added to form submit (e.g. redirect)
 * @var null|array $validation validation errors and messages (may be undefined, MUST USE NULL COALESCING)
 * @var bool $oldPasswordErr undefined or boolean value if old password was wrong
 */
?>

<?php
// Define assets that should be included
//$this->addAttribute('js', ['assets/auth/password-strength-checker.js']);
$this->addAttribute('css', ['assets/general/css/form.css']); ?>

<h2>Change password</h2>

<!-- If error flash array is not empty, error class is added to div -->
<div class="form-box <?= isset($formError) ? ' wrong-cred-input' : '' ?>">
    <form action="<?= $route->urlFor('change-password-submit') ?>"
          class="form" method="post" autocomplete="on">
        <?php
        // Display form error message if there is one
        if (isset($formErrorMessage)) { ?>
            <strong class="err-msg"><?= $formErrorMessage ?></strong>
            <?php
        } ?>

        <!--   Old password    -->
        <label for="old-password-inp">Old password</label>
        <input type="password" name="old_password" id="old-password-inp" minlength="3" required
               class="<?= //If old password is wrong, the variable is set by the server, otherwise undefined
               $oldPasswordErr ?? false ? 'wrong-cred-input' : '' ?>"
        >

        <!--   Password 1    -->
        <label for="password1-inp">New password</label>
        <input type="password" name="password" id="password1-inp" minlength="3" required
               class="<?= //If there is an error on a specific field, echo error class
               ($passwordErr = get_field_error(($validation ?? []), 'password')) ? 'wrong-cred-input' : '' ?>"
        >
        <?= isset($passwordErr) ? '<strong class="err-msg">' . $passwordErr . '</strong>' : '' ?>

        <!--   Password 2     -->
        <label for="password2-inp">Repeat new password</label>
        <input type="password" name="password2" id="password2-inp" minlength="3" required
               class="<?= //If there is an error on a specific field, echo error class
               ($password2Err = get_field_error(($validation ?? []), 'password2')) ? 'wrong-cred-input' : '' ?>
               <?= // If there is error with both passwords
               ($passwordsErr = get_field_error(($validation ?? []), 'passwords')) ? 'wrong-cred-input' : '' ?>"
        >
        <?= isset($password2Err) ? '<strong class="err-msg">' . $password2Err . '</strong>' : '' ?>
        <?= isset($passwordsErr) ? '<strong class="err-msg">' . $passwordsErr . '</strong>' : '' ?>

        <input type="submit" class="submit-btn" value="Set new password">
    </form>
    <br>Go back to the
    <a href="<?= $route->urlFor('profile-page', [], $queryParams ?? []) ?>">profile page</a>
</div>

<?php
// Throttle error message in request-throttle.html.php ?>

