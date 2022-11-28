<?php

/**
 * @var \Odan\Session\FlashInterface $flash
 * @var \Slim\Interfaces\RouteParserInterface $route
 * @var array $queryParams query params that should be added to form submit (e.g. redirect)
 * @var null|array $validation validation errors and messages (may be undefined, MUST USE NULL COALESCING)
 * @var string $basePath
 */

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
    // Define assets that should be included
    $this->addAttribute('css', ['assets/general/css/form.css']);
    // $this->addAttribute('js', ['assets/general/js/form-input-name-replacer.js']);

    // fetch() includes another template into the current template
    // Include template which contains HTML to include assets
    echo $this->fetch(
        'layout/assets.html.php', // Merge layout assets and from sub templates
        [
            'stylesheets' => [
                'assets/general/css/form.css',
                'assets/general/css/layout.css',
                'assets/general/css/general.css',
                'assets/general/css/default.css',
                'assets/authentication/login.css'
            ],
            // 'scripts' => array_merge($layoutJs, $js ?? []),
            // The type="module" allows the use of import and export inside a JS file.
            'jsModules' => ['assets/general/js/default.js'],
        ]
    );
    ?>

    <title>Slim Example Project</title>

</head>
<body>

<h2>Slim Example Project</h2>


<!-- If error flash array is not empty, error class is added to div -->
<div class="page-form-container <?= isset($formError) ? ' invalid-form' : '' ?>" id="login-form-container">
    <form action="<?= $route->urlFor('login-submit', [], $queryParams ?? []) ?>"
          id="login-form" class="form" method="post" autocomplete="on">

        <?= // General form error message if there is one
        isset($formErrorMessage) ? '<strong id="form-general-error-msg" class="error-panel">' . $formErrorMessage .
            '</strong>' : '' ?>

        <!-- ===== Email ===== -->
        <div class="form-input-group <?= //If there is an error on a specific field, echo error class
        ($emailErr = get_field_error(($validation ?? []), 'email')) ? ' input-group-error' : '' ?>">
            <label>Email</label>
            <input type="email" name="email"
                   maxlength="254"
                   required value="<?= $preloadValues['email'] ?? '' ?>">
            <?= isset($emailErr) ? '<strong class="err-msg">' . $emailErr . '</strong>' : '' ?>
        </div>

        <!-- ===== PASSWORD ===== -->
        <div class="form-input-group <?= //If there is an error on a specific field, echo error class
        ($passwordErr = get_field_error(($validation ?? []), 'password')) ? ' input-group-error' : '' ?>">
            <label>Password</label>
            <input type="password" id="loginPasswordInp" name="password" minlength="3" required>
            <?= isset($passwordErr) ? '<strong class="err-msg">' . $passwordErr . '</strong>' : '' ?>
            <a class="discrete-link content-below-input"
               href="<?= $route->urlFor('password-forgotten-page') ?>">Password forgotten</a>
        </div>
        <!-- reCaptcha -->
        <div class="g-recaptcha" id="recaptcha" data-sitekey="6LcctKoaAAAAAAcqzzgz-19OULxNxtwNPPS35DOU"></div>

        <input type="submit" class="submit-btn" id="submitBtnLogin" value="Login">
    </form>
    <?= $this->fetch('layout/request-throttle.html.php') ?>
</div>

</body>
</html>
