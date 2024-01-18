<?php

namespace App\Application\Action\Client\Page;

use App\Application\Renderer\TemplateRenderer;
use App\Domain\Authorization\Privilege;
use App\Domain\Client\Service\Authorization\ClientPermissionVerifier;
use App\Domain\Client\Service\ClientListFilter\ClientListFilterChipProvider;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class ClientListPageAction
{
    public function __construct(
        private TemplateRenderer $templateRenderer,
        private ClientListFilterChipProvider $clientListFilterChipGetter,
        private ClientPermissionVerifier $clientPermissionVerifier
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
            $this->clientPermissionVerifier->isGrantedToCreate() ? Privilege::CR->name : Privilege::N->name
        );

        return $this->templateRenderer->render($response, 'client/clients-list.html.php');
    }
}
