<?php

namespace App\Application\Actions\Client\Page;

use App\Application\Responder\Responder;
use App\Domain\Authorization\Privilege;
use App\Domain\Client\Authorization\ClientAuthorizationChecker;
use App\Domain\Client\Service\ClientListFilter\ClientListFilterChipProvider;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Action.
 */
final class ClientListPageAction
{
    /**
     * The constructor.
     *
     * @param Responder $responder The responder
     * @param ClientListFilterChipProvider $clientListFilterChipGetter
     * @param ClientAuthorizationChecker $clientAuthorizationChecker
     */
    public function __construct(
        private readonly Responder $responder,
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
        // Clients are loaded dynamically with js after page load for a faster loading time
        // Retrieving available filters
        $clientListFilters = $this->clientListFilterChipGetter->getActiveAndInactiveClientListFilters();

        $this->responder->addPhpViewAttribute('clientListFilters', $clientListFilters);
        $this->responder->addPhpViewAttribute(
            'clientCreatePrivilege',
            $this->clientAuthorizationChecker->isGrantedToCreate() ? Privilege::CREATE : Privilege::NONE
        );

        return $this->responder->render($response, 'client/clients-list.html.php');
    }
}
