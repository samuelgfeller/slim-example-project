<?php

namespace App\Modules\User\Action\Page;

use App\Core\Application\Responder\TemplateRenderer;
use App\Modules\Exception\Domain\DomainRecordNotFoundException;
use App\Modules\User\Enum\UserStatus;
use App\Modules\User\Service\UserFinder;
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
