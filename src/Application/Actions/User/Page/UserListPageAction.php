<?php

namespace App\Application\Actions\User\Page;

use App\Application\Responder\Responder;
use App\Domain\User\Authorization\UserAuthorizationChecker;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpForbiddenException;

/**
 * Action.
 */
final class UserListPageAction
{
    /**
     * The constructor.
     *
     * @param Responder $responder The responder
     * @param privatereadonlyUserAuthorizationChecker $userAuthorizationChecker
     */
    public function __construct(
        private readonly Responder $responder,
        private readonly UserAuthorizationChecker $userAuthorizationChecker,
    ) {
    }

    /**
     * Action.
     *
     * @param ServerRequestInterface $request The request
     * @param ResponseInterface $response The response
     * @param array $args
     *
     * @throws \Throwable
     * @throws \JsonException
     *
     * @return ResponseInterface The response
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        if ($this->userAuthorizationChecker->isGrantedToRead()) {
            return $this->responder->render($response, 'user/user-list.html.php');
        }

        throw new HttpForbiddenException($request, 'Not allowed to see this page.');
    }
}
