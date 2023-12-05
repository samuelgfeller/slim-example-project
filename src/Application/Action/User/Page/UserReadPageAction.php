<?php

namespace App\Application\Action\User\Page;

use App\Application\Responder\TemplateRenderer;
use App\Domain\Exception\DomainRecordNotFoundException;
use App\Domain\User\Enum\UserStatus;
use App\Domain\User\Service\UserFinder;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;

final readonly class UserReadPageAction
{
    public function __construct(
        private TemplateRenderer $templateRenderer,
        private UserFinder $userFinder,
        private SessionInterface $session,
    ) {
    }

    /**
     * Action.
     *
     * @param ServerRequestInterface $request The request
     * @param ResponseInterface $response The response
     * @param array $args
     *
     * @return ResponseInterface The response
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $authenticatedUserId = $this->session->get('user_id');
        $userId = (int)($args['user_id'] ?? $authenticatedUserId);
        try {
            // Retrieve user infos
            return $this->templateRenderer->render($response, 'user/user-read.html.php', [
                'user' => $this->userFinder->findUserReadResult($userId),
                'isOwnProfile' => $userId === $authenticatedUserId,
                // Get all user status cases as enums
                'userStatuses' => UserStatus::cases(),
            ]);
        } catch (DomainRecordNotFoundException $domainRecordNotFoundException) {
            throw new HttpNotFoundException($request, $domainRecordNotFoundException->getMessage());
        }
    }
}
