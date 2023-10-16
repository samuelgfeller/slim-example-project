<?php

namespace App\Application\Middleware;

use App\Application\Data\UserNetworkSessionData;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Middleware that adds client network data such as IP address and user agent
 * as well as user identity id to the clientNetworkData DTO.
 */
class UserNetworkSessionDataMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly UserNetworkSessionData $clientNetworkData,
        private readonly SessionInterface $session,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Server params will be null in testing
        $ipAddress = $request->getServerParams()['REMOTE_ADDR'];
        $userAgent = $request->getServerParams()['HTTP_USER_AGENT'];

        // Add ip address to the ipAddressData DTO object
        $this->clientNetworkData->ipAddress = $ipAddress;
        $this->clientNetworkData->userAgent = $userAgent;
        $this->clientNetworkData->userId = $this->session->get('user_id');

        return $handler->handle($request);
    }
}
