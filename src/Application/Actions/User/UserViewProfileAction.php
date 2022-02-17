<?php

namespace App\Application\Actions\User;

use App\Application\Responder\Responder;
use App\Domain\Factory\LoggerFactory;
use App\Domain\User\Service\UserFinder;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

/**
 * Action.
 */
final class UserViewProfileAction
{
    protected LoggerInterface $logger;

    /**
     * The constructor.
     *
     * @param Responder $responder The responder
     * @param LoggerFactory $logger
     * @param UserFinder $userFinder
     * @param SessionInterface $session
     */
    public function __construct(
        private Responder $responder,
        LoggerFactory $logger,
        private UserFinder $userFinder,
        private SessionInterface $session
    ) {
        $this->logger = $logger->addFileHandler('error.log')
            ->createInstance('user-view');
    }

    /**
     * Action.
     *
     * @param ServerRequestInterface $request The request
     * @param ResponseInterface $response The response
     *
     * @param array $args The routing arguments
     * @return ResponseInterface The response
     * @throws \JsonException
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        // Check if user is logged in
        if (($userId = $this->session->get('user_id')) !== null) {
            $user = $this->userFinder->findUserById($userId);
            return $this->responder->render(
                $response,
                'user/profile.html.php',
                // Provide same query params passed to login page to be added to the login submit request
                ['user' => $user]
            );
        }

        // Not logged in - redirection to login page done in UserAuthenticationMiddleware
        return $response;

    }
}
