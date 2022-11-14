<?php

namespace App\Application\Actions\User;

use App\Application\Responder\Responder;
use App\Domain\Exceptions\DomainRecordNotFoundException;
use App\Domain\Exceptions\ForbiddenException;
use App\Domain\User\Enum\UserStatus;
use App\Domain\User\Service\UserFinder;
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
     */
    public function __construct(
        private readonly Responder $responder,
        private readonly UserFinder $userFinder,
    ) {
    }

    /**
     * Action.
     *
     * @param ServerRequestInterface $request The request
     * @param ResponseInterface $response The response
     *
     * @param array $args
     * @return ResponseInterface The response
     * @throws \JsonException
     * @throws \Throwable
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        try {
            // Retrieve user infos
            return $this->responder->render($response, 'user/user-read.html.php', [
                'user' => $this->userFinder->findUserReadResult((int)$args['user_id']),
                'userStatuses' => UserStatus::cases(),
            ]);
        } catch (ForbiddenException $forbiddenException) {
            throw new HttpForbiddenException($request, $forbiddenException->getMessage());
        } catch (DomainRecordNotFoundException $domainRecordNotFoundException){
            throw new HttpNotFoundException($request, $domainRecordNotFoundException->getMessage());
        }
    }
}
