<?php

/**@var \Odan\Session\FlashInterface $flash
 * @var \Slim\Interfaces\RouteParserInterface $route
 * @var \Slim\Views\PhpRenderer $this
 */

$this->setLayout('layout.html.php');
?>

<!-- Define assets that should be included -->
<?php
$this->addAttribute('css', ['assets/general/css/form.css']);
$this->addAttribute('js', ['assets/auth/auth.js']);
?>
<div class="verticalCenter">
    <h2 style="display:inline-block;">One last step</h2>
    <p>To verify that you are the owner of the email address, please click on the link you in your email inbox.</p>
</div>

