<?php

/**
 * @var \Slim\Views\PhpRenderer $this
 * @var \Odan\Session\FlashInterface $flash
 * @var \Slim\Interfaces\RouteParserInterface $route
 * @var array $queryParams query params that should be added to form submit (e.g. redirect)
 * @var null|array $validation validation errors and messages (may be undefined, MUST USE NULL COALESCING)
 * @var string $basePath
 * @var array $config 'public' configuration values
 */

// Remove layout if there was a default
$this->setLayout('');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!--  Trailing slash has to be avoided on asset paths. Otherwise, <base> does not work  -->
    <base href="<?= html($basePath) ?>/"/>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon"/>
    <?php
    // fetch() includes another template in the current template
    // Include template which contains HTML to include assets
    echo $this->fetch(
        'layout/assets.html.php',
        [
            'stylesheets' => [
                'assets/general/page-component/flash-message/flash-message.css',
                'assets/general/page-component/form/form.css',
                'assets/general/general-css/layout.css',
                'assets/general/general-css/general.css',
                'assets/general/general-css/colors.css',
                'assets/general/general-font/fonts.css',
                'assets/authentication/login.css'
            ],
            // The type="module" allows the use of import and export inside a JS file.
            'jsModules' => ['assets/general/general-js/initialization.js', 'assets/authentication/login-main.js'],
        ]
    );
    ?>

    <title>Login - <?= html($config['app_name']) ?></title>

</head>
<body>
<?= $this->fetch('layout/flash-messages.html.php') ?>

<h2><?= html($config['app_name']) ?></h2>

<!-- If error flash array is not empty, error class is added to div -->
<div class="page-form-container <?= isset($formError) ? ' invalid-form' : '' ?>" id="login-form-container">
    <form action="<?= $route->urlFor('login-submit', [], $queryParams ?? []) ?>"
          id="login-form" class="form" method="post" autocomplete="on">

        <?= // General form error message if there is one
        isset($formErrorMessage) ? '<strong id="form-general-error-msg" class="error-panel">' .
            /*Form error message is hardcoded in the backend with styling html tags*/
            $formErrorMessage .
            '</strong>' : '' ?>

        <!-- ===== Email ===== -->
        <div class="form-input-div <?= isset($validation['email']) ? ' input-group-error' : '' ?>">
            <label for="email-input"><?= __('E-Mail') ?></label>
            <input type="email" name="email"
                   maxlength="254" id="email-input"
                   required value="<?= html($preloadValues['email'] ?? '') ?>">
            <?= isset($validation['email']) ? '<strong class="err-msg">'
                . html($validation['email'][0]) . '</strong>' : '' ?>
            <span class="subdued-text content-below-input cursor-pointer" id="discrete-login-toggle-btn">
                <?= __('Login') ?></span>
        </div>

        <!-- ===== PASSWORD ===== -->
        <div id="password-input-div"
             class="form-input-div<?= //If there is an error on a specific field, echo error class
             isset($validation['password']) ? ' input-group-error' : '' ?>">
            <label for="password-input"><?= __('Password') ?></label>
            <input type="password" id="password-input" name="password" minlength="3" required>
            <?= isset($validation['password']) ?
                '<strong class="err-msg">' . html($validation['password'][0]) . '</strong>' : '' ?>
            <span class="subdued-text content-below-input cursor-pointer"
                  id="password-forgotten-btn"><?= __('Password forgotten') ?></span>
        </div>
        <div class="clearfix"></div>
        <!-- reCaptcha -->
        <div class="g-recaptcha" id="recaptcha" data-sitekey="6LcctKoaAAAAAAcqzzgz-19OULxNxtwNPPS35DOU"></div>
        <input type="submit" class="submit-btn" id="submitBtnLogin" value="<?= __('Login') ?>"
               data-request-password-label="<?= __('Request password') ?>">
        <?= $this->fetch('layout/request-throttle.html.php') ?>
    </form>
</div>
</body>
</html>
