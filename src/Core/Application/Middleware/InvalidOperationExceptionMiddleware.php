<?php

namespace App\Core\Application\Middleware;

use App\Core\Application\Data\UserNetworkSessionData;
use App\Core\Application\Responder\JsonResponder;
use App\Core\Domain\Exception\InvalidOperationException;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

final readonly class InvalidOperationExceptionMiddleware implements MiddlewareInterface
{
    public function __construct(
        private ResponseFactoryInterface $responseFactory,
        private JsonResponder $jsonResponder,
        private UserNetworkSessionData $userNetworkSessionData,
        private LoggerInterface $logger,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (InvalidOperationException $exception) {
            $response = $this->responseFactory->createResponse();

            $this->logger->notice(
                'Invalid operation from user ' . $this->userNetworkSessionData->userId . ' on ' .
                $request->getUri()->getPath() . ' with message: ' . $exception->getMessage()
            );

            return $this->jsonResponder->encodeAndAddToResponse(
                $response,
                [
                    'status' => 'error',
                    'message' => $exception->getMessage(),
                ],
                StatusCodeInterface::STATUS_BAD_REQUEST
            );
        }
    }
}
