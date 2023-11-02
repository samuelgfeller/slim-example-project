<?php

namespace App\Application\Middleware;

use App\Application\Data\UserNetworkSessionData;
use App\Application\Responder\Responder;
use App\Domain\Exception\InvalidOperationException;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class InvalidOperationExceptionMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly Responder $responder,
        private readonly UserNetworkSessionData $userNetworkSessionData,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (InvalidOperationException $exception) {
            $response = $this->responder->createResponse();

            $this->logger->notice(
                'Invalid operation from user ' . $this->userNetworkSessionData->userId . ' on ' .
                $request->getUri()->getPath() . ' with message: ' . $exception->getMessage()
            );

            return $this->responder->respondWithJson(
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
