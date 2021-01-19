<?php /**
 * @var \Odan\Session\FlashInterface $flash
 * @var \Slim\Interfaces\RouteParserInterface $route
 */ ?>

<!-- Include stylesheets temp solution to include css until SLE-77 found a solution -->
<style><?php require_once 'assets/general/css/form.css' ?></style>


<div class="verticalCenter">
    <h2 style="display:inline-block;">Register</h2>
</div>

<div class="form-box" id="register-form-box">

    <form class="form" autocomplete="on" id="<?= $route->urlFor('register-submit') ?>">
        <label for="registerNameInp">Name</label>
        <input type="text" name="name" id="registerNameInp"
               placeholder="John Doe"
               maxlength="200"
               minlength="2"
               autofocus
               required>
        <label for="registerEmailInp">Email</label>
        <input type="email" name="email" id="registerEmailInp"
               placeholder="your@email.com"
               maxlength="254"
               required>
        <label for="registerPassword1Inp">Password</label>
        <input type="password" name="password" id="registerPassword1Inp" minlength="3"
               required>
        <label for="registerPassword2Inp">Repeat password</label>
        <input type="password" name="password2" id="registerPassword2Inp" minlength="3"
               required>
        <button type="button" class="submitBtn" id="registerSubmitBtn">Create account</button>

    </form>
    <br>Do you already have an account? <a href="<?= $route->urlFor('login-page') ?>">Login</a>
</div>
<script src="assets/auth/auth.js"></script>

<!--    </div>
</div>

<script src="/js/config.js"></script>
<script src="/js/pageContentManager.js"></script>
<script src="/js/general_scripts.js"></script>
<script src="/js/modal.js"></script>


</body>
</html>
-->