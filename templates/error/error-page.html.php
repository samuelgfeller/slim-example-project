<?php
/**
 * @var \Slim\Interfaces\RouteParserInterface $route
 * @var array $errorMessage containing (int) statusCode; (string) reasonPhrase; (string) exceptionMessage
 * @var \Slim\Views\PhpRenderer $this
 */
$this->setLayout('layout.html.php');
?>
<?php
// Define assets that should be included
$this->addAttribute('css', ['assets/error/error.css']); ?>


<section id="cloud-section" class="">
    <div class="cloud small-cloud"><span><?= html($errorMessage['statusCode']) ?></span></div>
    <div class="cloud big-cloud"><span>&#129301;</span></div>
</section>
<section id="error-description-section">
    <?php
    switch ($errorMessage['statusCode']) {
        case 404:
            $title = 'Nothing but clouds here.';
            $message = 'Try to navigate with the menu or <a href="mailto:contact@samuel-gfeller.ch">contact me</a>.';
            break;
        case 403:
            $title = 'Access forbidden.';
            $message = 'You are not allowed to access this page. Please contact an administrator.';
            break;
        case 400:
            $title = 'The request is invalid';
            $message = 'There is something wrong with the request. <br>Please try again and 
<a href="mailto:contact@samuel-gfeller.ch">contact me</a>.';
            break;
        case 422:
            $title = 'Validation failed.';
            $message = 'Please try again with valid data or <a href="mailto:contact@samuel-gfeller.ch">contact me</a>.';
            break;
        case 500:
            $title = 'Internal Server Error.';
            $message = 'It\'s not your fault! The server has an internal error. <br> Please try again and 
<a href="mailto:contact@samuel-gfeller.ch">ping me</a> so I can have a look.';
            break;
        default:
            $title = 'An error occurred.';
            $message = 'Bad thing is that there is an error, but the good thing is that it\'s fixable! <br>
Please try again and then <a href="mailto:contact@samuel-gfeller.ch">contact me</a>.';
            break;
    }
    ?>
    <h2 id="title"><?= html($title) ?></h2>
    <p><?= $message /* Not escape with html() because message contains html that should be interpreted*/ ?></p>
    <?= $errorMessage['exceptionMessage'] !== null ? '<p>Error message: <b>' . $errorMessage['exceptionMessage'] . '</b></p>' : '' ?>
</section>
<section id="home-btn-section">
    <a href="<?= $route->urlFor('home-page') ?>" class="btn">Go back home</a>
</section>

