<?php

namespace App\Application\Middleware;

use App\Domain\Factory\LoggerFactory;
use ErrorException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

/**
 * Middleware which sets set_error_handler() to custom DefaultErrorHandler
 * and logs warning and notices.
 */
final class ErrorHandlerMiddleware implements MiddlewareInterface
{
    private bool $displayErrorDetails;

    private bool $logErrors;

    private LoggerInterface $logger;

    /**
     * @param bool $displayErrorDetails
     * @param bool $logErrors
     * @param LoggerFactory $logger
     */
    public function __construct(
        bool $displayErrorDetails,
        bool $logErrors,
        LoggerFactory $logger
    ) {
        $this->displayErrorDetails = $displayErrorDetails;
        $this->logErrors = $logErrors;
        $this->logger = $logger->addFileHandler('error.log')
            ->createInstance('nonfatal-error');
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
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        // Only make notices / wantings to ErrorException's if error details should be displayed
        // SLE-57 Making warnings and notices to exceptions in dev
        // set_error_handler does not handle fatal errors so this function gets not called by fatal errors
        set_error_handler(
            function ($severity, $message, $file, $line) {
                if (error_reporting() & $severity) {
                    // Log all non fatal errors
                    if ($this->logErrors) {
                        // If error is warning
                        if ($severity === E_WARNING | E_CORE_WARNING | E_COMPILE_WARNING | E_USER_WARNING) {
                            $this->logger->warning(
                                "Warning [$severity] $message on line $line in file $file"
                            );
                        } // If error is non fatal but not warning (default)
                        else {
                            $this->logger->notice(
                                "Notice [$severity] $message on line $line in file $file"
                            );
                        }
                    }
                    // Throwing an exception allows us to have a stack trace and more error details useful in dev
                    if ($this->displayErrorDetails === true) {
                        // Logging for fatal errors happens in DefaultErrorHandler.php
                        throw new ErrorException($message, 0, $severity, $file, $line);
                    }
                }

                return true;
            }
        );

        return $handler->handle($request);
    }
}
