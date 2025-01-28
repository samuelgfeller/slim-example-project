<?php

namespace App\Module\User\Read\Action;

use App\Application\Responder\TemplateRenderer;
use App\Domain\Exception\DomainRecordNotFoundException;
use App\Module\User\Enum\UserStatus;
use App\Module\User\Read\Service\UserReadFinder;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;

final readonly class UserReadPageAction
{
    public function __construct(
        private TemplateRenderer $templateRenderer,
        private UserReadFinder $userReadFinder,
        private SessionInterface $session,
    ) {
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args,
    ): ResponseInterface {
        $authenticatedUserId = $this->session->get('user_id');
        $userId = (int)($args['user_id'] ?? $authenticatedUserId);
        try {
            // Retrieve user infos
            return $this->templateRenderer->render($response, 'user/user-read.html.php', [
                'user' => $this->userReadFinder->findUserReadResult($userId),
                'isOwnProfile' => $userId === $authenticatedUserId,
                // Get all user status cases as enums
                'userStatuses' => UserStatus::cases(),
            ]);
        } catch (DomainRecordNotFoundException $domainRecordNotFoundException) {
            throw new HttpNotFoundException($request, $domainRecordNotFoundException->getMessage());
        }
    }
}
