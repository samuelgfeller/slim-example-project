<?php

namespace App\Application\Action\User\Page;

use App\Application\Responder\TemplateRenderer;
use App\Domain\User\Service\Authorization\UserPermissionVerifier;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpForbiddenException;

final class UserListPageAction
{
    public function __construct(
        private readonly TemplateRenderer $templateRenderer,
        private readonly UserPermissionVerifier $userPermissionVerifier,
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
        if ($this->userPermissionVerifier->isGrantedToRead()) {
            return $this->templateRenderer->render($response, 'user/user-list.html.php');
        }

        throw new HttpForbiddenException($request, 'Not allowed to see this page.');
    }
}
