<?php

$this->setLayout('layout.html.php');
/**
 * @var \Odan\Session\FlashInterface $flash
 * @var \Slim\Interfaces\RouteParserInterface $route
 * @var array $queryParams query params that should be added to form submit (e.g. redirect)
 * @var null|array $validation validation errors and messages (may be undefined, MUST USE NULL COALESCING)
 */
?>

<?php
// Define assets that should be included
$this->addAttribute('css', ['assets/general/css/form.css']); ?>

<h2>Password recovery</h2>

<!-- If error flash array is not empty, error class is added to div -->
<div class="form-container <?= isset($formError) ? ' invalid-input' : '' ?>" id="password-forgotten-form-container">
    <form action="<?= $route->urlFor('password-forgotten-email-submit') ?>"
          class="form" method="post" autocomplete="on">

        <?= // General form error message if there is one
        isset($formErrorMessage) ? '<strong id="form-general-error-msg" class="error-panel">' . $formErrorMessage .
            '</strong>' : '' ?>

        <div class="form-input-group <?= //If there is an error on a specific field, echo error class
        ($emailErr = get_field_error(($validation ?? []), 'email')) ? ' input-group-error' : '' ?>">
            <input type="email" name="email" id="resetPasswordEmailInp" value="<?= $preloadValues['email'] ?? '' ?>"
                   maxlength="254" required>
            <label for="resetPasswordEmailInp">Email</label>
            <?= isset($emailErr) ? '<strong class="err-msg">' . $emailErr . '</strong>' : '' ?>
        </div>
        <input type="submit" class="submit-btn" value="Request link to reset password">
    </form>
    <br>
    <span class="discrete-link">Or do you want to navigate to the
    <a href="<?= $route->urlFor('register-page', [], $queryParams ?? []) ?>">login page</a></span>
</div>

