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
$this->addAttribute('jsModules', ['assets/auth/password-strength-checker.js']);
$this->addAttribute('css', ['assets/general/page-component/form/form.css']); ?>

<h2>Change password</h2>

<!-- If error flash array is not empty, error class is added to div -->
<div class="page-form-container <?= isset($formError) ? ' form-error' : '' ?>">
    <form action="<?= $route->urlFor('change-password-submit') ?>"
          class="form" method="post" autocomplete="on">

        <?= // General form error message if there is one
        isset($formErrorMessage) ? '<strong id="form-general-error-msg" class="error-panel">' . $formErrorMessage .
            '</strong>' : '' ?>

        <!--   Old password    -->
        <div class="form-input-div <?= //If old password is wrong, the variable is set by the server, otherwise undefined
        $oldPasswordErr ?? false ? ' input-group-error' : '' ?>">
            <input type="password" name="old_password" id="old-password-inp" minlength="3" required>
            <label for="old-password-inp">Old password</label>
        </div>
        <!--   Password 1    -->
        <div id="password1-input-div" class="form-input-div <?= //If there is an error on a specific field, echo error class
        ($passwordErr = get_field_error(($validation ?? []), 'password')) ? ' input-group-error' : '' ?>">
            <input type="password" name="password" id="password1-input" minlength="3" required>
            <label for="password1-input">New password</label>
            <?= isset($passwordErr) ? '<strong class="err-msg">' . $passwordErr . '</strong>' : '' ?>
        </div>

        <!--   Password 2     -->
        <div class="form-input-div <?= //If there is an error on a specific field, echo error class
        ($password2Err = get_field_error(($validation ?? []), 'password2')) ||
        ($passwordsErr = get_field_error(($validation ?? []), 'passwords')) ? ' input-group-error' : '' ?>">
            <input type="password" name="password2" id="password2-input" minlength="3" required>
            <label for="password2-input">Repeat new password</label>
            <?= isset($password2Err) ? '<strong class="err-msg">' . $password2Err . '</strong>' : '' ?>
            <?= isset($passwordsErr) ? '<strong class="err-msg">' . $passwordsErr . '</strong>' : '' ?>
        </div>

        <input type="submit" class="submit-btn" value="Set new password">
    </form>
</div>
    <span class="secondary-text"><br>Go back to the
    <a href="<?= $route->urlFor('profile-page', [], $queryParams ?? []) ?>">profile page</a></span>

<?php
// Throttle error message in request-throttle.html.php ?>

