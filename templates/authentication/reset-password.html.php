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
 * @var array $config 'public' configuration values
 */

// Remove layout if there was a default
$this->setLayout('');
?>

<!DOCTYPE html>
<html lang="<?= setlocale(LC_ALL, 0) ?>">
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
                'assets/general/general-css/colors.css',
                'assets/general/general-font/fonts.css',
                'assets/general/general-css/layout.css',
                'assets/general/general-css/general.css',
                'assets/authentication/login.css',
                'assets/general/page-component/flash-message/flash-message.css',
                'assets/general/page-component/form/form.css',
            ],
            // The type="module" allows the use of import and export inside a JS file.
            'jsModules' => ['assets/general/general-js/initialization.js', 'assets/authentication/password-reset-main.js'],
        ]
    );
    ?>

    <title>Reset password - <?= html($config['app_name']) ?></title>

</head>
<body>

<h2><?= __('Reset password') ?></h2>

<!-- If error flash array is not empty, error class is added to div -->
<div class="page-form-container <?= isset($formError) ? ' invalid-input' : '' ?>">
    <form action="<?= $route->urlFor('password-reset-submit') ?>"
          class="form" method="post" autocomplete="on">

        <?= // General form error message if there is one
        isset($formErrorMessage) ? '<strong id="form-general-error-msg" class="error-panel">'
            . /*Form error message is hardcoded in the backend with styling html tags*/$formErrorMessage . '</strong>' : '' ?>

        <!--   Password 1    -->
        <div class="form-input-div <?= isset($validation['password']) ? ' input-group-error' : '' ?>">
            <label for="password1-input"><?= __('Password') ?></label>
            <input type="password" name="password" id="password1-input" minlength="3" required
                   autocomplete="new-password">
            <?= isset($validation['password']) ? '<strong class="err-msg">'
                . html($validation['password'][0]) . '</strong>' : '' ?>
        </div>

        <!--   Password 2     -->
        <div class="form-input-div <?= isset($validation['password2']) ? ' input-group-error' : '' ?>"
             style="margin-bottom: 0;">
            <label for="password2-input"><?= __('Repeat password') ?></label>
            <input type="password" name="password2" id="password2-input" minlength="3" required
                   autocomplete="new-password">
            <?= isset($validation['password2']) ?
                '<strong class="err-msg">' . html($validation['password2'][0]) . '</strong>' : '' ?>
        </div>
        <?= /*In case passwords don't match, there may be a second error for password2 */
        isset($validation['password2'][1]) ? '<strong class="err-msg">' . html($validation['password2'][1])
            . '</strong>' : '' ?>
        <a href="login" class="subdued-text content-below-input cursor-pointer"><?= __('Login') ?></a>

        <input type="hidden" name="token" value="<?= html($token) ?>">
        <input type="hidden" name="id" value="<?= html($id) ?>">
        <input type="submit" id="password-reset-submit-btn" class="submit-btn" style="margin-top: 20px"
               value="<?= __('Set new password') ?>">
        <?= $this->fetch('layout/request-throttle.html.php') ?>
    </form>
</div>

</body>
</html>

