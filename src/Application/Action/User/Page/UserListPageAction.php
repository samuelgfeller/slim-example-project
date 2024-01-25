<?php

namespace App\Application\Action\User\Page;

use App\Application\Renderer\TemplateRenderer;
use App\Domain\User\Service\Authorization\UserPermissionVerifier;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpForbiddenException;

final readonly class UserListPageAction
{
    public function __construct(
        private TemplateRenderer $templateRenderer,
        private UserPermissionVerifier $userPermissionVerifier,
    ) {
    }

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
