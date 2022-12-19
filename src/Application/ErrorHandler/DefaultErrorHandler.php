<?php

namespace App\Application\ErrorHandler;

use App\Domain\Factory\LoggerFactory;
use App\Domain\Validation\ValidationException;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpException;
use Slim\Views\PhpRenderer;
use Throwable;

/**
 * Default Error Renderer.
 */
class DefaultErrorHandler
{
    private LoggerInterface $logger;

    /**
     * The constructor.
     *
     * @param PhpRenderer $phpRenderer PHP-View renderer
     * @param ResponseFactoryInterface $responseFactory The response factory
     * @param LoggerFactory $logger Logger
     */
    public function __construct(
        private readonly PhpRenderer $phpRenderer,
        private readonly ResponseFactoryInterface $responseFactory,
        LoggerFactory $logger
    ) {
        $this->logger = $logger->addFileHandler('error.log')->createInstance('error');
    }

    /**
     * Invoke.
     *
     * @param ServerRequestInterface $request The request
     * @param Throwable $exception The exception
     * @param bool $displayErrorDetails Show error details
     * @param bool $logErrors Log errors
     *
     * @throws Throwable
     *
     * @return ResponseInterface The response
     */
    public function __invoke(
        ServerRequestInterface $request,
        Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors
    ): ResponseInterface {
        // Log error
        // ErrorException was configured to be thrown with set_error_handler which is for non-fatal errors
        // They are logged in ErrorHandlerMiddleware.php and not here because if displayErrorDetails is false
        // ErrorException is not thrown and they wouldn't be logged in prod
        if ($logErrors && !$exception instanceof \ErrorException) {
            // Error with no stack trace https://stackoverflow.com/a/2520056/9013718
            $this->logger->error(
                sprintf(
                    'Error: [%s] %s File %s:%s , Method: %s, Path: %s',
                    $exception->getCode(),
                    $exception->getMessage(),
                    $exception->getFile(),
                    $exception->getLine(),
                    $request->getMethod(),
                    $request->getUri()->getPath()
                )
            );
        }

        // Error output if script is called via cli (e.g. testing)
        if (PHP_SAPI === 'cli') {
            // If the column is not found and the request is coming from the command line, it probably means
            // that the database and code was changed and `composer migration:generate` and `composer schema:generate`
            // were not executed after the change.
            if ($exception instanceof \PDOException
                && str_contains($exception->getMessage(), 'Column not found')
            ) {
                echo "Column not existing. Try running `composer schema:generate` in the console and run tests again. \n";
            }
            // The exception is thrown to have the standard behaviour (important for testing)
            throw $exception;
        }

        // Detect status code
        $statusCode = $this->getHttpStatusCode($exception);
        $reasonPhrase = $this->responseFactory->createResponse()->withStatus($statusCode)->getReasonPhrase();

        // Depending on displayErrorDetails different error infos will be shared
        if ($displayErrorDetails === true) {
            $errorMessage = $this->getExceptionDetailsAsHtml($exception, $statusCode, $reasonPhrase);
            $errorTemplate = 'error/error-details.html.php'; // If this path fails, the default exception is shown
        } else {
            // If its a HttpException it's safe to show the error message to the user (used for custom )
            $exceptionMessage = $exception instanceof HttpException ? $exception->getMessage() : null;
            $errorMessage = [
                'exceptionMessage' => $exceptionMessage,
                'statusCode' => $statusCode,
                'reasonPhrase' => $reasonPhrase,
            ];
            $errorTemplate = 'error/error-page.html.php';
        }

        // Create response
        $response = $this->responseFactory->createResponse();

        // Render template
        $response = $this->phpRenderer->render(
            $response,
            $errorTemplate,
            ['errorMessage' => $errorMessage]
        );

        return $response->withStatus($statusCode);
    }

    /**
     * Get http status code.
     *
     * @param Throwable $exception The exception
     *
     * @return int The http code
     */
    private function getHttpStatusCode(Throwable $exception): int
    {
        // Detect status code
        $statusCode = StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR; // 500

        if ($exception instanceof HttpException) {
            $statusCode = (int)$exception->getCode();
        }

        if ($exception instanceof ValidationException) {
            $statusCode = StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY; // 422
        }

        $file = basename($exception->getFile());
        if ($file === 'CallableResolver.php') {
            $statusCode = StatusCodeInterface::STATUS_NOT_FOUND; // 404
        }

        return $statusCode;
    }

    /**
     * Build HTML with exception content and styling.
     *
     * @param Throwable $exception Error
     * @param int|null $statusCode
     * @param string|null $reasonPhrase
     *
     * @return string The full error message
     */
    private function getExceptionDetailsAsHtml(
        Throwable $exception,
        int $statusCode = null,
        string $reasonPhrase = null
    ): string {
        // Init variables
        $error = '';

        $file = $exception->getFile();
        $line = $exception->getLine();
        $message = $exception->getMessage();
        $trace = $exception->getTrace();

        // Check if it is a warning message or error
        $errorCssClass = $exception instanceof \ErrorException ? 'warning' : 'error';

        // prepare path to be more readable https://stackoverflow.com/a/9891884/9013718
        $lastBackslash = strrpos($file, '\\');
        $lastWord = substr($file, $lastBackslash + 1);
        $firstChunkFullPath = substr($file, 0, $lastBackslash + 1);
        // remove C:\xampp\htdocs\ and project name to keep only part starting with src\
        $firstChunkMinusFilesystem = str_replace('C:\xampp\htdocs\\', '', $firstChunkFullPath);
        // locate project name because it is right before the first backslash (after removing filesystem)
        $projectName = substr($firstChunkMinusFilesystem, 0, strpos($firstChunkMinusFilesystem, '\\') + 1);
        // remove project name from first chunk
        $firstChunk = str_replace($projectName, '', $firstChunkMinusFilesystem);

        // build error html page
        $error .= sprintf('<body class="%s">', $errorCssClass); // open body
        $error .= sprintf('<div id="title-div" class="%s">', $errorCssClass); // opened title div
        if ($statusCode !== null && $reasonPhrase !== null) {
            $error .= sprintf(
                '<p><span>%s | %s</span><span id="exception-name">%s</span></p>',
                $statusCode,
                $reasonPhrase,
                get_class($exception)
            );
        }
        $error .= sprintf(
            '<h1>%s in <span id="first-path-chunk">%s </span>%s on line %s.</h1></div>', // closed title div
            $message,
            $firstChunk,
            $lastWord,
            $line
        ); // close title div

        $error .= sprintf('<div id="trace-div" class="%s"><table>', $errorCssClass); // opened trace div / opened table
        $error .= '<tr><th id="num-th">#</th><th>Function</th><th>Location</th></tr>';
        foreach ($trace as $key => $t) {
            // Sometimes class, type, file and line not set e.g. pdfRenderer when var undefined in template
            $t['class'] = $t['class'] ?? '';
            $t['type'] = $t['type'] ?? '';
            $t['file'] = $t['file'] ?? '';
            $t['line'] = $t['line'] ?? '';
            // remove everything from file path before the last \
            $fileWithoutPath = $this->removeEverythingBeforeChar($t['file']);
            // remove everything from class before late \
            $classWithoutPath = $this->removeEverythingBeforeChar($t['class']);
            // if file path has not vendor in it, a css class is added to indicate it because it's more relevant
            $nonVendorClass = !strpos($t['file'], 'vendor') ? 'non-vendor' : '';
            // adding html
            $error .= sprintf(
                '<tr><td>%s</td><td class="function-td %s">%s</td><td class="%s">%s</td></tr>',
                $key,
                $nonVendorClass,
                '...\\' . $classWithoutPath . $t['type'] . $t['function'] . '(...)', // only last 85 chars
                $nonVendorClass,
                '...\\' . $fileWithoutPath . ':<span class="lineSpan">' . $t['line'] . '</span>',
            );
        }
        $error .= '</table></div>'; // close table
        $error .= '<style>
            @font-face { font-family: CenturyGothic; src: url(assets/general/general-font/CenturyGothic.ttf); }
            * { white-space: break-spaces; overflow-wrap: anywhere; }
            body { margin: 0; background: #ffd9d0; font-family: "CenturyGothic", CenturyGothic, Geneva, AppleGothic, sans-serif; }
            body.warning { background: #ffead0; }
            body.error { background: #ffd9d0; }
            #title-div{ padding: 5px 10%; color: black; margin:30px; background: tomato; border-radius: 0 35px; box-shadow: 0 0 17px tomato; }
            #title-div h1 { margin-top: 4px; }
            #title-div.warning { background: orange; box-shadow: 0 0 17px orange;}
            #title-div.error { background: tomato; box-shadow: 0 0 17px tomato;}
            #first-path-chunk{ font-size: 0.7em; }
            #trace-div{ width: 80%; margin: auto auto 40px; min-width: 688px; padding: 20px; background: #ff9e88; border-radius: 0 35px;
                 box-shadow: 0 0 10px #ff856e; }
            #trace-div.warning { background: #ffc588; box-shadow: 0 0 10px #ffad6e; }
            #trace-div.error { background: #ff9e88; box-shadow: 0 0 10px #ff856e; }
            #trace-div h2{ margin-top: 0; padding-top: 19px; text-align: center; }
            #trace-div table{ border-collapse: collapse;  font-size: 1.2em; width: 100%; overflow-x: auto; }
            #trace-div table td, #trace-div table th{  /*border-top: 6px solid red;*/ padding: 8px; text-align: left;}
            #trace-div table tr td:first-child, #trace-div table tr th:first-child { padding-left: 20px; }
            #num-th { font-size: 2em; color: #a46856; margin-right: 50px;}
            .non-vendor{ font-weight: bold; } 
            .non-vendor .lineSpan{ font-weight: bold; color: #b00000;font-size: 1.3em; } 
            #exception-name { float: right}
            @media screen and (max-width: 1000px) {
                #trace-div { font-size: 0.8em; }
                #title-div h1 { font-size: 1.6em; }
            }
            @media screen and (max-width: 810px) {
                #trace-div table { font-size: 1.1em; }
                #title-div { box-sizing: border-box; margin-left: 0; margin-right: 0; width: 100%; }
            }
            
            </style>';
        $error .= '</body>'; // close body

        return $error;
    }

    private function removeEverythingBeforeChar(string $string, string $lastChar = '\\'): string
    {
        return trim(substr($string, strrpos($string, $lastChar) + 1));

        // alternative https://coderwall.com/p/cpxxxw/php-get-class-name-without-namespace
//        $path = explode('\\', __CLASS__);
//        return array_pop($path);
    }
}
