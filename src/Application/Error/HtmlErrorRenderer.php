<?php
/**
 * From https://akrabat.com/custom-error-rendering-in-slim-4/
 */

namespace App\Application\Error;

use Slim\Exception\HttpInternalServerErrorException;
use Slim\Exception\HttpNotFoundException;
use Slim\Interfaces\ErrorRendererInterface;
use Throwable;

final class HtmlErrorRenderer
{
    public function __invoke(Throwable $exception, bool $displayErrorDetails): string
    {
        // https://discourse.slimframework.com/t/slim-errors-returns-500-on-php-syntax-errors/2949/7

        $title = 'Error';
        $message = 'An error has occurred.';

        if ($exception instanceof HttpNotFoundException) {
            $title = 'Page not found';
            $message = 'This page could not be found.';
        }

        return $this->renderHtmlPage($title, $message);
    }

    public function renderHtmlPage(string $title = '', string $message = ''): string
    {
        $title = htmlentities($title, ENT_COMPAT | ENT_HTML5, 'utf-8');
        $message = htmlentities($message, ENT_COMPAT | ENT_HTML5, 'utf-8');

        return <<<EOT
<!DOCTYPE html>
<html>
<head>
  <title>$title - My website</title>
  <link rel="stylesheet"
     href="https://cdnjs.cloudflare.com/ajax/libs/mini.css/3.0.1/mini-default.css">
</head>
<body>
  <h1>$title</h1>
  <p>$message</p>
</body>
</html>
EOT;
    }
}