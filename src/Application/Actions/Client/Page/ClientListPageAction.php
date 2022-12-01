<?php

namespace App\Application\Actions\Client\Page;

use App\Application\Responder\Responder;
use App\Domain\Authorization\Privilege;
use App\Domain\Client\Authorization\ClientAuthorizationChecker;
use App\Domain\ClientListFilter\ClientListFilterSetter;
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
     */
    public function __construct(
        private readonly Responder $responder,
        private readonly ClientListFilterSetter $clientListFilterSetter,
        private readonly ClientAuthorizationChecker $clientAuthorizationChecker
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
        // Clients are loaded dynamically with js after page load for a faster loading time
        // Retrieving available filters
        $clientListFilters = $this->clientListFilterSetter->findClientListFilters();
        $this->responder->addPhpViewAttribute('clientListFilters', $clientListFilters);
        $this->responder->addPhpViewAttribute(
            'clientCreatePrivilege',
            $this->clientAuthorizationChecker->isGrantedToCreate() ? Privilege::CREATE : Privilege::NONE
        );
        return $this->responder->render($response, 'client/clients-list.html.php');
    }
}
