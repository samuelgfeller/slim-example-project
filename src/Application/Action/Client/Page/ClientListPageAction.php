<?php

namespace App\Application\Action\Client\Page;

use App\Application\Responder\TemplateRenderer;
use App\Domain\Authorization\Privilege;
use App\Domain\Client\Service\Authorization\ClientAuthorizationChecker;
use App\Domain\Client\Service\ClientListFilter\ClientListFilterChipProvider;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ClientListPageAction
{
    public function __construct(
        private readonly TemplateRenderer $templateRenderer,
        private readonly ClientListFilterChipProvider $clientListFilterChipGetter,
        private readonly ClientAuthorizationChecker $clientAuthorizationChecker
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
     *
     * @return ResponseInterface The response
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        // Clients are loaded dynamically with js after page load for a faster loading time
        // Retrieving available filters
        $clientListFilters = $this->clientListFilterChipGetter->getActiveAndInactiveClientListFilters();

        $this->templateRenderer->addPhpViewAttribute('clientListFilters', $clientListFilters);
        $this->templateRenderer->addPhpViewAttribute(
            'clientCreatePrivilege',
            $this->clientAuthorizationChecker->isGrantedToCreate() ? Privilege::CREATE : Privilege::NONE
        );

        return $this->templateRenderer->render($response, 'client/clients-list.html.php');
    }
}