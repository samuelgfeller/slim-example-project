<?php


namespace App\Application\Middleware;

use App\Factory\LoggerFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

/**
 * Middleware.
 */
final class ErrorHandlerMiddleware implements MiddlewareInterface
{
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * The constructor.
     *
     * @param LoggerInterface $loggerInterface The logger
     */
    public function __construct(LoggerInterface $loggerInterface)
    {
        $this->logger = $loggerInterface;
    }

    /**
     * Invoke middleware.
     *
     * @param ServerRequestInterface $request The request
     * @param RequestHandlerInterface $handler The handler
     *
     * @return ResponseInterface The response
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $errorTypes = E_ALL;

        // Set custom php error handler
        set_error_handler(
            function ($errno, $errstr, $errfile, $errline) {
                switch ($errno) {
                    case E_ERROR:
                    case E_CORE_ERROR:
                    case E_COMPILE_ERROR:
                    case E_PARSE:
                    case E_USER_ERROR:
                    case E_RECOVERABLE_ERROR:
                    case E_STRICT:
                        $this->logger->error(
                            "Error number [$errno] $errstr on line $errline in file $errfile"
                        );
                        break;
                    case E_WARNING:
                    case E_CORE_WARNING:
                    case E_COMPILE_WARNING:
                    case E_USER_WARNING:
                        $this->logger->warning(
                            "Error Number [$errno] $errstr on line $errline in file $errfile"
                        );
                        break;
                    default:
                        $this->logger->notice(
                            "Error number [$errno] $errstr on line $errline in file $errfile"
                        );
                        break;
                }

//                if(error_reporting()!==0) // Not error suppression operator @
//                    throw new \ErrorException($strMessage, /*nExceptionCode*/ 0, $nSeverity, $strFilePath, $nLineNumber);

                // Don't execute PHP internal error handler
                return true;
            },
            $errorTypes
        );

        return $handler->handle($request);
    }
}
