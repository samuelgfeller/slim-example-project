<?php

/**
 * @var \Slim\Interfaces\RouteParserInterface $route
 * @var array $errorMessage containing (int) statusCode; (string) reasonPhrase; (string) exceptionMessage
 * @var \Slim\Views\PhpRenderer $this
 * @var array $config public config values
 */
$this->setLayout('layout.html.php');
?>
<?php
// Define assets that should be included
$this->addAttribute('css', ['assets/error/error.css']);
$this->addAttribute('js', ['assets/error/error.js']);
?>

<section id="error-inner-section">
    <h1 id="error-status-code"><?= html($errorMessage['statusCode']) ?></h1>

    <section id="error-description-section">
        <?php
        switch ($errorMessage['statusCode']) {
            case 404:
                $title = 'Page not found';
                $message = __("Looks like you've ventured into uncharted territory. Please report the issue!");
                break;
            case 403:
                $title = 'Access forbidden';
                $message = __(
                    'You are not allowed to access this page. Please report the issue if you think this is 
                an error.'
                );
                break;
            case 400:
                $title = 'The request is invalid';
                $message = __('There is something wrong with the request syntax. Please report the issue.');
                break;
            case 422:
                $title = 'Validation failed.';
                $message = __(
                    'The server could not interpret the data it received. Please try again with valid data and
                report the issue if it persists.'
                );
                break;
            case 500:
                $title = 'Internal Server Error.';
                $message = __(
                    'It\'s not your fault! The server has an internal error. <br> Please try again and 
                    report the issue if the problem persists.'
                );
                break;
            default:
                $title = 'An error occurred.';
                $message = __(
                    'While it\'s unfortunate that an error exists, the silver lining is that it can be rectified! 
<br>Please try again and then contact us.'
                );
                break;
        }
        $emailSubject = strip_tags(str_replace('"', '', $errorMessage['exceptionMessage']))
            ?? $errorMessage['statusCode'] . ' ' . $title;
        $emailBody = __('Please explain what you did prior to the error happening.');
        ?>
        <h2 id="error-reason-phrase">OOPS! <?= html($title) ?></h2>
        <p id="error-message"><?= $message /* Not escape with html() because $message is safe and has html tags */ ?></p>
        <?= $errorMessage['exceptionMessage'] !== null ?
            '<p id="server-message">Server message: ' . $errorMessage['exceptionMessage'] . '</p>' : '' ?>

    </section>
    <section id="error-btn-section">
        <a href="<?= $route?->urlFor('home-page') ?>" class="btn"><?= __('Go back home') ?></a>
        <a href="mailto:<?= ($config['email']['main_contact_address'] ?? 'contact@samuel-gfeller.ch')
        . '?subject=' . $emailSubject . '&body=' . $emailBody ?>" target="_blank" class="btn">
            <?= __('Report the issue') ?></a>
    </section>
</section>


