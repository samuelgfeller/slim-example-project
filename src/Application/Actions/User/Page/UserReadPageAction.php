<?php

namespace App\Application\Actions\User\Page;

use App\Application\Responder\Responder;
use App\Domain\Authentication\Exception\ForbiddenException;
use App\Domain\Exception\DomainRecordNotFoundException;
use App\Domain\User\Enum\UserStatus;
use App\Domain\User\Service\UserFinder;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpNotFoundException;

/**
 * Action.
 */
final class UserReadPageAction
{
    /**
     * The constructor.
     *
     * @param Responder $responder The responder
     * @param UserFinder $userFinder
     * @param SessionInterface $session
     */
    public function __construct(
        private readonly Responder $responder,
        private readonly UserFinder $userFinder,
        private readonly SessionInterface $session,
    ) {
    }

    /**
     * Action.
     *
     * @param ServerRequestInterface $request The request
     * @param ResponseInterface $response The response
     * @param array $args
     *
     * @throws \JsonException
     * @throws \Throwable
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
            return $this->responder->render($response, 'user/user-read.html.php', [
                'user' => $this->userFinder->findUserReadResult($userId),
                'isOwnProfile' => $userId === $authenticatedUserId,
                'userStatuses' => UserStatus::cases(),
            ]);
        } catch (ForbiddenException $forbiddenException) {
            throw new HttpForbiddenException($request, $forbiddenException->getMessage());
        } catch (DomainRecordNotFoundException $domainRecordNotFoundException) {
            throw new HttpNotFoundException($request, $domainRecordNotFoundException->getMessage());
        }
    }
}
