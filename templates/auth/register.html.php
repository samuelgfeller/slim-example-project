<?php $this->setLayout('layout.html.php');
/**@var \Odan\Session\FlashInterface $flash
 * @var \Slim\Interfaces\RouteParserInterface $route
 */
?>

<!-- Define assets that should be included -->
<?php $this->addAttribute('css', ['assets/general/css/form.css']); ?>
<?php $this->addAttribute('js', ['assets/auth/auth.js']); ?>

<div class="verticalCenter">
    <h2 style="display:inline-block;">Register</h2>
</div>

<div class="form-box" id="register-form-box">

    <form class="form" autocomplete="on" id="<?= $route->urlFor('register-submit') ?>" method="post">
        <label for="register-name-inp">Name</label>
        <input type="text" name="name" id="register-name-inp"
               placeholder="John Doe"
               maxlength="200"
               minlength="2"
               autofocus
               required>
        <label for="register-email-inp">Email</label>
        <input type="email" name="email" id="register-email-inp"
               placeholder="your@email.com"
               maxlength="254"
               required>
        <label for="register-password1-inp">Password</label>
        <input type="password" name="password" id="register-password1-inp" minlength="3"
               required>
        <label for="register-password2-inp">Repeat password</label>
        <input type="password" name="password2" id="register-password2-inp" minlength="3"
               required>
        <input type="submit" class="submit-btn" id="register-submit-btn" value="Create account">

    </form>
    <br>Do you already have an account? <a href="<?= $route->urlFor('login-page') ?>">Login</a>
</div>
