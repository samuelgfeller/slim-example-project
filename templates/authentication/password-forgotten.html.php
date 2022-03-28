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
<div class="form-box <?= isset($formError) ? ' wrong-cred-input' : '' ?>" id="password-forgotten-form-box">
    <form action="<?= $route->urlFor('password-forgotten-email-submit') ?>"
          class="form" method="post" autocomplete="on">
        <?php
        // Display form error message if there is one
        if (isset($formErrorMessage)) { ?>
            <strong class="err-msg"><?= $formErrorMessage ?></strong>
            <?php
        } ?>
        <label for="resetPasswordEmailInp">Email</label>
        <input type="email" name="email" id="resetPasswordEmailInp"
               placeholder="your@email.com"
               maxlength="254"
               required
               class="<?= //If there is an error on a specific field, echo error class
               ($emailErr = get_field_error(($validation ?? []), 'email')) ? 'wrong-cred-input' : '' ?>"
        >
        <?= isset($emailErr) ? '<strong class="err-msg">' . $emailErr . '</strong>' : '' ?>

        <input type="submit" class="submit-btn" id="submitBtnLogin" value="Login">
    </form>
    <br>Or do you want to navigate to the
    <a href="<?= $route->urlFor('register-page', [], $queryParams ?? []) ?>">login page</a>?
</div>

<?php
// Throttle error message in request-throttle.html.php ?>

