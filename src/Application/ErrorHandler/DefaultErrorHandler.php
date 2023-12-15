<?php

namespace App\Application\ErrorHandler;

use App\Domain\Validation\ValidationException;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpException;
use Slim\Views\PhpRenderer;
use Throwable;

readonly class DefaultErrorHandler
{
    public function __construct(
        private PhpRenderer $phpRenderer,
        private ResponseFactoryInterface $responseFactory,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * Invoke.
     *
     * @param ServerRequestInterface $request The request
     * @param Throwable $exception The exception
     * @param bool $displayErrorDetails Show error details
     * @param bool $logErrors Log errors
     * @param bool $logErrorDetails Log errors with details
     *
     * @return ResponseInterface The response
     * @throws Throwable
     *
     */
    public function __invoke(
        ServerRequestInterface $request,
        Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ): ResponseInterface {
        // Log error
        // ErrorException was configured to be thrown with set_error_handler which is for non-fatal errors
        // They are logged in ErrorHandlerMiddleware.php and not here because if displayErrorDetails is false
        // ErrorException is not thrown, and they wouldn't be logged in prod
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
            if ($exception instanceof \PDOException && str_contains($exception->getMessage(), 'Column not found')) {
                echo "Column not existing. Try running `composer schema:generate` in the console and run tests again. \n";
            }
            // The exception is thrown to have the standard behaviour (important for testing)
            throw $exception;
        }

        // Create response
        $response = $this->responseFactory->createResponse();

        // Detect status code
        $statusCode = $this->getHttpStatusCode($exception);
        $response = $response->withStatus($statusCode);
        $reasonPhrase = $response->getReasonPhrase();

        // Depending on displayErrorDetails different error infos will be shared
        if ($displayErrorDetails === true) {
            $detailsAttributes = $this->getExceptionDetailsAttributes($exception, $statusCode, $reasonPhrase);

            // Render template if the template path fails, the default webserver exception is shown
            return $this->phpRenderer->render($response, 'error/error-details.html.php', $detailsAttributes);
        }

        // Display generic error page
        // If it's a HttpException it's safe to show the error message to the user
        $exceptionMessage = $exception instanceof HttpException ? $exception->getMessage() : null;
        $errorMessage = [
            'exceptionMessage' => $exceptionMessage,
            'statusCode' => $statusCode,
            'reasonPhrase' => $reasonPhrase,
        ];

        // Render template
        return $this->phpRenderer->render($response, 'error/error-page.html.php', ['errorMessage' => $errorMessage]);
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
     * @return array
     */
    private function getExceptionDetailsAttributes(
        Throwable $exception,
        ?int $statusCode = null,
        ?string $reasonPhrase = null
    ): array {
        $file = $exception->getFile();
        $lineNumber = $exception->getLine();
        $exceptionMessage = $exception->getMessage();
        $trace = $exception->getTrace();

        // Check if it is a warning message or error
        $severityClassName = $exception instanceof \ErrorException ? 'warning' : 'error';

        // prepare path to be more readable https://stackoverflow.com/a/9891884/9013718
        $lastBackslash = strrpos($file, '\\');
        $mainErrorFile = substr($file, $lastBackslash + 1);
        $firstChunkFullPath = substr($file, 0, $lastBackslash + 1);
        // remove C:\xampp\htdocs\ and project name to keep only part starting with src\
        $firstChunkMinusFilesystem = str_replace('C:\xampp\htdocs\\', '', $firstChunkFullPath);
        // locate project name because it is right before the first backslash (after removing filesystem)
        $projectName = substr($firstChunkMinusFilesystem, 0, strpos($firstChunkMinusFilesystem, '\\') + 1);
        // remove project name from first chunk
        $pathToMainErrorFile = str_replace($projectName, '', $firstChunkMinusFilesystem);

        $traceEntries = [];

        foreach ($trace as $key => $t) {
            // Sometimes class, type, file and line not set e.g. pdfRenderer when var undefined in template
            $t['class'] = $t['class'] ?? '';
            $t['type'] = $t['type'] ?? '';
            $t['file'] = $t['file'] ?? '';
            $t['line'] = $t['line'] ?? '';
            // remove everything from file path before the last \
            $fileWithoutPath = $this->removeEverythingBeforeChar($t['file']);
            // remove everything from class before last \
            $classWithoutPath = $this->removeEverythingBeforeChar($t['class']);
            // if the file path doesn't contain "vendor", a css class is added to highlight it
            $nonVendorFileClass = !str_contains($t['file'], 'vendor') ? 'non-vendor' : '';
            // if file and class path don't contain vendor, add "non-vendor" css class to add highlight on class
            $classIsVendor = str_contains($t['class'], 'vendor');
            $nonVendorFunctionCallClass = !empty($nonVendorFileClass) && !$classIsVendor ? 'non-vendor' : '';
            // Get function arguments
            $args = [];
            foreach ($t['args'] ?? [] as $argument) {
                $args[] = $this->getTraceArgumentAsString($argument);
            }
            $args = implode(', ', $args);
            $traceEntries[$key]['args'] = $args;
            $traceEntries[$key]['nonVendorClass'] = $nonVendorFileClass;
            // Function call happens in a class outside the vendor folder
            // File may be non-vendor, but function call of the same trace entry is in a vendor class
            $traceEntries[$key]['nonVendorFunctionCallClass'] = $nonVendorFunctionCallClass;
            $traceEntries[$key]['classAndFunction'] = $classWithoutPath . $t['type'] . $t['function'];
            $traceEntries[$key]['fileName'] = $fileWithoutPath;
            $traceEntries[$key]['line'] = $t['line'];
        }

        return [
            'severityClassName' => $severityClassName,
            'statusCode' => $statusCode,
            'reasonPhrase' => $reasonPhrase,
            'exceptionClassName' => get_class($exception),
            'exceptionMessage' => $exceptionMessage,
            'pathToMainErrorFile' => $pathToMainErrorFile,
            'mainErrorFile' => $mainErrorFile,
            'errorLineNumber' => $lineNumber,
            'traceEntries' => $traceEntries,
        ];
    }

    private function removeEverythingBeforeChar(string $string, string $lastChar = '\\'): string
    {
        return trim(substr($string, strrpos($string, $lastChar) + 1));

        // alternative https://coderwall.com/p/cpxxxw/php-get-class-name-without-namespace
        //        $path = explode('\\', __CLASS__);
        //        return array_pop($path);
    }

    /**
     * The stack trace contains called functions with function arguments
     * that can be of any type (objects, arrays, strings or null).
     * This function returns such an argument as string.
     *
     * @param mixed $argument
     *
     * @return string
     */
    private function getTraceArgumentAsString(mixed $argument): string
    {
        if ($argument === null) {
            $formatted = 'NULL';
        } elseif (is_string($argument)) {
            if (strlen($argument) > 15) {
                $argument = substr($argument, 0, 15) . '...';
            }
            $formatted = '"' . $argument . '"';
        } elseif (is_object($argument)) {
            $formatted = get_class($argument);
            $nonVendor = str_starts_with($formatted, 'App');
            // Only keep the last part of class string
            if (strlen($formatted) > 15) {
                $formatted = trim(substr($formatted, strrpos($formatted, '\\') + 1));
            }
            $formatted = $nonVendor ? "<b>Object($formatted)</b>" : "Object($formatted)";
        } elseif (is_array($argument)) {
            // Convert each array element to string recursively
            $elements = array_map(function ($element) {
                return $this->getTraceArgumentAsString($element);
            }, $argument);

            return '[' . implode(', ', $elements) . ']';
        } else {
            $formatted = (string)$argument;
        }

        return $formatted;
    }
}
