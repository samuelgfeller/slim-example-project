<?php

namespace App\Module\User\ListPage\Action;

use App\Application\Responder\TemplateRenderer;
use App\Module\User\Read\Service\UserReadAuthorizationChecker;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpForbiddenException;

final readonly class UserListPageAction
{
    public function __construct(
        private TemplateRenderer $templateRenderer,
        private UserReadAuthorizationChecker $userReadAuthorizationChecker,
    ) {
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args,
    ): ResponseInterface {
        if ($this->userReadAuthorizationChecker->isGrantedToRead()) {
            return $this->templateRenderer->render($response, 'user/user-list.html.php');
        }

        throw new HttpForbiddenException($request, 'Not allowed to see this page.');
    }
}
