<?php

namespace App\Module\Client\List\Action;

use App\Application\Responder\TemplateRenderer;
use App\Module\Authorization\Enum\Privilege;
use App\Module\Client\Create\Service\ClientCreateAuthorizationChecker;
use App\Module\Client\List\Domain\Service\ClientListFilterChipProvider;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class ClientListPageAction
{
    public function __construct(
        private TemplateRenderer $templateRenderer,
        private ClientListFilterChipProvider $clientListFilterChipGetter,
        private ClientCreateAuthorizationChecker $clientCreateAuthorizationChecker,
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
            $this->clientCreateAuthorizationChecker->isGrantedToCreate() ? Privilege::CR->name : Privilege::N->name
        );

        return $this->templateRenderer->render($response, 'client/clients-list.html.php');
    }
}
