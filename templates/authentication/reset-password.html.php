<?php

/**
 * Create new password after forgotten with token
 *
 * @var \Odan\Session\FlashInterface $flash
 * @var \Slim\Interfaces\RouteParserInterface $route
 * @var array $queryParams query params that should be added to form submit (e.g. redirect)
 * @var null|array $validation validation errors and messages (may be undefined, MUST USE NULL COALESCING)
 * @var string $token verification token
 * @var string $id verification id
 * @var string $basePath
 */

// Remove layout if there was a default
$this->setLayout('');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!--  Trailing slash has to be avoided on asset paths. Otherwise, <base> does not work  -->
    <base href="<?= $basePath ?>/"/>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="assets/favicon.ico" type="image/x-icon"/>
    <?php
    // fetch() includes another template into the current template
    // Include template which contains HTML to include assets
    echo $this->fetch(
        'layout/assets.html.php',
        [
            'stylesheets' => [
                'assets/general/page-component/flash-message/flash-message.css',
                'assets/general/page-component/form/form.css',
                'assets/general/general-css/layout.css',
                'assets/general/general-css/general.css',
                'assets/general/general-css/default.css',
                'assets/authentication/login.css'
            ],
            // The type="module" allows the use of import and export inside a JS file.
            'jsModules' => ['assets/general/general-js/default.js', 'assets/authentication/password-reset-main.js'],
        ]
    );
    ?>

    <title>Reset password - Slim Example Project</title>

</head>
<body>

<h2>Reset password</h2>

<!-- If error flash array is not empty, error class is added to div -->
<div class="page-form-container <?= isset($formError) ? ' invalid-input' : '' ?>">
    <form action="<?= $route->urlFor('password-reset-submit') ?>"
          class="form" method="post" autocomplete="on">

        <?= // General form error message if there is one
        isset($formErrorMessage) ? '<strong id="form-general-error-msg" class="error-panel">' . $formErrorMessage .
            '</strong>' : '' ?>

        <?php
        // If error for both passwords
        $passwordsErr = get_field_error(($validation ?? []), 'passwords');
        ?>

        <!--   Password 1    -->
        <div class="form-input-div <?= //If there is an error on a specific field, echo error class
        ($passwordErr = get_field_error(($validation ?? []), 'password')) ||
        $passwordsErr ? ' input-group-error' : '' ?>">
            <label for="password1-input">Password</label>
            <input type="password" name="password" id="password1-input" minlength="3" required>
            <?= isset($passwordErr) ? '<strong class="err-msg">' . $passwordErr . '</strong>' : '' ?>
        </div>

        <!--   Password 2     -->
        <div class="form-input-div <?= //If there is an error on a specific field, echo error class
        ($password2Err = get_field_error(($validation ?? []), 'password2')) ||
        $passwordsErr ? ' input-group-error' : '' ?>">
            <label for="password2-input">Repeat password</label>
            <input type="password" name="password2" id="password2-input" minlength="3" required>
            <?= isset($password2Err) ? '<strong class="err-msg">' . $password2Err . '</strong>' : '' ?>
        </div>

        <?= isset($passwordsErr) ? '<strong class="err-msg">' . $passwordsErr . '</strong>' : '' ?>

        <input type="hidden" name="token" value="<?= $token ?? null ?>">
        <input type="hidden" name="id" value="<?= $id ?? null ?>">
        <a href="login" class="discrete-text content-below-input cursor-pointer">Login</a>
        <input type="submit" id="password-reset-submit-btn" class="submit-btn" value="Set new password">
    </form>
    <?= $this->fetch('layout/request-throttle.html.php') ?>
</div>

</body>
</html>

