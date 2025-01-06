<?php

namespace App\Module\Client\Action\Page;

use App\Core\Application\Responder\TemplateRenderer;
use App\Module\Authorization\Enum\Privilege;
use App\Module\Client\Domain\Service\Authorization\ClientPermissionVerifier;
use App\Module\Client\Domain\Service\ClientListFilter\ClientListFilterChipProvider;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class ClientListPageAction
{
    public function __construct(
        private TemplateRenderer $templateRenderer,
        private ClientListFilterChipProvider $clientListFilterChipGetter,
        private ClientPermissionVerifier $clientPermissionVerifier,
    ) {
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args,
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
