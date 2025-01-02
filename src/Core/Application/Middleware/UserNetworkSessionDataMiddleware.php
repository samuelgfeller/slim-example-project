<?php

namespace App\Core\Application\Middleware;

use App\Core\Application\Data\UserNetworkSessionData;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Middleware that adds client network data such as IP address and user agent
 * as well as user identity id to the clientNetworkData DTO.
 */
final readonly class UserNetworkSessionDataMiddleware implements MiddlewareInterface
{
    public function __construct(
        // The UserNetworkSessionData DTO object is registered and created in the container definition
        // container.php so that this middleware can populate it with data that are also available
        // by any service that also injects this DTO object.
        private UserNetworkSessionData $clientNetworkData,
        private SessionInterface $session,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Server params will be null in testing
        $ipAddress = $request->getServerParams()['REMOTE_ADDR'] ?? null;
        $userAgent = $request->getServerParams()['HTTP_USER_AGENT'] ?? null;

        // Add ip address to the ipAddressData DTO object
        $this->clientNetworkData->ipAddress = $ipAddress;
        $this->clientNetworkData->userAgent = $userAgent;

        // Only initialize userId if it exists in session
        if ($userIdFromSession = $this->session->get('user_id')) {
            $this->clientNetworkData->userId = $userIdFromSession;
        }

        return $handler->handle($request);
    }
}
