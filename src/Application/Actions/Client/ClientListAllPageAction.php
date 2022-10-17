<?php

namespace App\Application\Actions\Client;

use App\Application\Responder\Responder;
use App\Domain\ClientListFilter\ClientListFilterSetter;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Action.
 */
final class ClientListAllPageAction
{
    /**
     * The constructor.
     *
     * @param Responder $responder The responder
     */
    public function __construct(
        private readonly Responder $responder,
        private readonly ClientListFilterSetter $clientListFilterSetter
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
        $clientListFilters = $this->clientListFilterSetter->findClientListFilters();
        $this->responder->addPhpViewAttribute('clientListFilters', $clientListFilters);
        // Loading the page. All posts are loaded dynamically with js after page load for a fast loading time
        return $this->responder->render($response, 'client/clients-list.html.php');
    }
}
