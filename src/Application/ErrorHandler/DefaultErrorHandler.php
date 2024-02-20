<?php

namespace App\Application\ErrorHandler;

use App\Domain\Validation\ValidationException;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Selective\BasePath\BasePathDetector;
use Slim\Exception\HttpException;
use Slim\Views\PhpRenderer;
use Throwable;

final readonly class DefaultErrorHandler
{
    private string $fileSystemPath;

    public function __construct(
        private PhpRenderer $phpRenderer,
        private ResponseFactoryInterface $responseFactory,
        private LoggerInterface $logger,
    ) {
        // The filesystem path to the project root folder will be removed in the error details page
        $this->fileSystemPath = 'C:\xampp\htdocs\\';
    }

    /**
     * @param ServerRequestInterface $request
     * @param Throwable $exception
     * @param bool $displayErrorDetails
     * @param bool $logErrors
     * @param bool $logErrorDetails
     *
     * @throws Throwable
     * @throws \ErrorException
     */
    public function __invoke(
        ServerRequestInterface $request,
        Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ): ResponseInterface {
        // Log error
        // If exception is an instance of ErrorException it means that the NonFatalErrorHandlerMiddleware
        // threw the exception for a warning or notice.
        // That middleware already logged the message, so it doesn't have to be done here.
        // The reason it is logged there is that if displayErrorDetails is false, ErrorException is not
        // thrown and the warnings and notices still have to be logged in prod.
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
            // that the database schema.sql was not updated after a change.
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
        // Reason phrase is the text that describes the status code e.g. 404 => Not found
        $reasonPhrase = $response->getReasonPhrase();

        $phpRendererAttributes['statusCode'] = $statusCode;
        $phpRendererAttributes['reasonPhrase'] = $reasonPhrase;

        // If $displayErrorDetails is true, display exception details
        if ($displayErrorDetails === true) {
            // Add exception details to template attributes
            $phpRendererAttributes = array_merge(
                $phpRendererAttributes,
                $this->getExceptionDetailsAttributes($exception)
            );
            // The error-details template does not include the default layout,
            // so the base path to the project root folder is required to load assets
            $phpRendererAttributes['basePath'] = (new BasePathDetector($request->getServerParams()))->getBasePath();

            // Render template if the template path fails, the default webserver exception is shown
            return $this->phpRenderer->render($response, 'error/error-details.html.php', $phpRendererAttributes);
        }

        // Display generic error page
        // If it's a HttpException it's safe to show the error message to the user
        $exceptionMessage = $exception instanceof HttpException ? $exception->getMessage() : null;
        $phpRendererAttributes['exceptionMessage'] = $exceptionMessage;

        // Render template
        return $this->phpRenderer->render($response, 'error/error-page.html.php', $phpRendererAttributes);
    }

    /**
     * Determine http status code.
     *
     * @param Throwable $exception The exception
     *
     * @return int The http code
     */
    private function getHttpStatusCode(Throwable $exception): int
    {
        // Default status code
        $statusCode = StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR; // 500

        // HttpExceptions have a status code
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
     * Build the attribute array for the detailed error page.
     *
     * @param Throwable $exception
     *
     * @return array
     */
    private function getExceptionDetailsAttributes(Throwable $exception): array
    {
        $file = $exception->getFile();
        $lineNumber = $exception->getLine();
        $exceptionMessage = $exception->getMessage();
        $trace = $exception->getTrace();

        // If the exception is ErrorException, the css class is warning, otherwise it's error
        $severityCssClassName = $exception instanceof \ErrorException ? 'warning' : 'error';

        // Remove the filesystem path and make the path to the file that had the error smaller to increase readability
        $lastBackslash = strrpos($file, '\\');
        $mainErrorFile = substr($file, $lastBackslash + 1);
        $firstChunkFullPath = substr($file, 0, $lastBackslash + 1);
        // remove C:\xampp\htdocs\ and project name to keep only part starting with src\
        $firstChunkMinusFilesystem = str_replace($this->fileSystemPath, '', $firstChunkFullPath);
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
            $fileWithoutPath = $this->removeEverythingBeforeLastBackslash($t['file']);
            // remove everything from class before last \
            $classWithoutPath = $this->removeEverythingBeforeLastBackslash($t['class']);
            // if the file path doesn't contain "vendor", a css class is added to highlight it
            $nonVendorFileClass = !str_contains($t['file'], 'vendor') ? 'non-vendor' : '';
            // if file and class path don't contain vendor, add "non-vendor" css class to add highlight on class
            $classIsVendor = str_contains($t['class'], 'vendor');
            $nonVendorFunctionCallClass = !empty($nonVendorFileClass) && !$classIsVendor ? 'non-vendor' : '';
            // Get function arguments
            $args = [];
            foreach ($t['args'] ?? [] as $argKey => $argument) {
                // Get argument as string not longer than 15 characters
                $args[$argKey]['truncated'] = $this->getTraceArgumentAsTruncatedString($argument);
                // Get full length of argument as string
                $fullArgument = $this->getTraceArgumentAsString($argument);
                // Replace double backslash with single backslash
                $args[$argKey]['detailed'] = str_replace('\\\\', '\\', $fullArgument);
            }
            $traceEntries[$key]['args'] = $args;
            // If the file is outside vendor class, add "non-vendor" css class to highlight it
            $traceEntries[$key]['nonVendorClass'] = $nonVendorFileClass;
            // Function call happens in a class outside the vendor folder
            // File may be non-vendor, but function call of the same trace entry is in a vendor class
            $traceEntries[$key]['nonVendorFunctionCallClass'] = $nonVendorFunctionCallClass;
            $traceEntries[$key]['classAndFunction'] = $classWithoutPath . $t['type'] . $t['function'];
            $traceEntries[$key]['fileName'] = $fileWithoutPath;
            $traceEntries[$key]['line'] = $t['line'];
        }

        return [
            'severityCssClassName' => $severityCssClassName,
            'exceptionClassName' => get_class($exception),
            'exceptionMessage' => $exceptionMessage,
            'pathToMainErrorFile' => $pathToMainErrorFile,
            'mainErrorFile' => $mainErrorFile,
            'errorLineNumber' => $lineNumber,
            'traceEntries' => $traceEntries,
        ];
    }

    /**
     * The stack trace contains the functions that are called during script execution with
     * function arguments that can be any type (objects, arrays, strings or null).
     * This function returns the argument as a string.
     *
     * @param mixed $argument
     *
     * @return string
     */
    private function getTraceArgumentAsString(mixed $argument): string
    {
        // If the variable is an object, return its class name.
        if (is_object($argument)) {
            return get_class($argument);
        }

        // If the variable is an array, iterate over its elements
        if (is_array($argument)) {
            $result = [];
            foreach ($argument as $key => $value) {
                // if it's an object, get its class name if it's an array represent it as 'Array'
                // otherwise, keep the original value.
                if (is_object($value)) {
                    $result[$key] = get_class($value);
                } elseif (is_array($value)) {
                    $result[$key] = 'Array';
                } else {
                    $result[$key] = $value;
                }
            }

            // Return the array converted to a string using var_export
            return var_export($result, true);
        }

        // If the variable is not an object or an array, convert it to a string using var_export.
        return var_export($argument, true);
    }

    /**
     * Convert the given argument to a string not longer than 15 chars
     * except if it's a file or a class name.
     *
     * @param mixed $argument the variable to be converted to a string
     *
     * @return string the string representation of the variable
     */
    private function getTraceArgumentAsTruncatedString(mixed $argument): string
    {
        if ($argument === null) {
            $formatted = 'NULL';
        } elseif (is_string($argument)) {
            // If string contains backslashes keep part after the last backslash, otherwise keep the first 15 chars
            if (str_contains($argument, '\\')) {
                $argument = $this->removeEverythingBeforeLastBackslash($argument);
            } elseif (strlen($argument) > 15) {
                $argument = substr($argument, 0, 15) . '...';
            }
            $formatted = '"' . $argument . '"';
        } elseif (is_object($argument)) {
            $formatted = get_class($argument);
            // Only keep the last part of class string
            if (strlen($formatted) > 15 && str_contains($formatted, '\\')) {
                $formatted = $this->removeEverythingBeforeLastBackslash($formatted);
            }
        } elseif (is_array($argument)) {
            // Convert each array element to string recursively
            $elements = array_map(function ($element) {
                return $this->getTraceArgumentAsTruncatedString($element);
            }, $argument);

            return '[' . implode(', ', $elements) . ']';
        } else {
            $formatted = (string)$argument;
        }

        return $formatted;
    }

    /**
     * If a string is 'App\Domain\Example\Class', this function returns 'Class'.
     *
     * @param string $string
     *
     * @return string
     */
    private function removeEverythingBeforeLastBackslash(string $string): string
    {
        return trim(substr($string, strrpos($string, '\\') + 1));
    }
}
