<?php

namespace App\Application\Middleware;

use App\Application\Exceptions\CorsMiddlewareException;
use App\Domain\Factory\LoggerFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

final class CorsMiddlewareExceptionMiddleware implements MiddlewareInterface
{
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * The constructor.
     *
     * @param LoggerFactory $logger The logger factory
     */
    public function __construct(LoggerFactory $logger)
    {
        $this->logger = $logger->addFileHandler('error.log')
            ->createInstance('§-login');
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
        try {
            return $handler->handle($request);
        } catch (CorsMiddlewareException $exception) {
            // Logging
            $this->logger->error($exception->getMessage());

            // Return the CORS response
            return $exception->getResponse();
        }
    }
}
