<?php

namespace App\Application\Middleware;

use ErrorException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

/**
 * Handles non-fatal errors such as warnings and notices.
 */
final readonly class NonFatalErrorHandlerMiddleware implements MiddlewareInterface
{
    private bool $displayErrorDetails;
    private bool $logErrors;

    public function __construct(bool $displayErrorDetails, bool $logErrors, private LoggerInterface $logger)
    {
        $this->displayErrorDetails = $displayErrorDetails;
        $this->logErrors = $logErrors;
    }

    /**
     * Invoke middleware.
     *
     * @param ServerRequestInterface $request The request
     * @param RequestHandlerInterface $handler The handler
     *
     * @throws ErrorException
     *
     * @return ResponseInterface The response
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Only make notices / wantings to ErrorException's if error details should be displayed
        // SLE-57 Making warnings and notices to exceptions for development
        // set_error_handler only handles non-fatal errors. The function callback is not called by fatal errors.
        set_error_handler(
            function ($severity, $message, $file, $line) {
                // Don't throw exception if error reporting is turned off.
                // '&' checks if a particular error level is included in the result of error_reporting().
                if (error_reporting() & $severity) {
                    // Log non fatal errors if logging is enabled
                    if ($this->logErrors) {
                        // If error is warning
                        if ($severity === E_WARNING | E_CORE_WARNING | E_COMPILE_WARNING | E_USER_WARNING) {
                            $this->logger->warning("Warning [$severity] $message on line $line in file $file");
                        } // If error is non-fatal and is not a warning
                        else {
                            $this->logger->notice("Notice [$severity] $message on line $line in file $file");
                        }
                    }
                    if ($this->displayErrorDetails === true) {
                        // Throw ErrorException to stop script execution and have access to more error details
                        // Logging for fatal errors happens in DefaultErrorHandler.php
                        throw new ErrorException($message, 0, $severity, $file, $line);
                    }
                }

                return true;
            }
        );

        $response = $handler->handle($request);

        // Restore previous error handler in post-processing to satisfy PHPUnit 11 that checks for any
        // leftover error handlers https://github.com/sebastianbergmann/phpunit/pull/5619
        restore_error_handler();

        return $response;
    }
}
