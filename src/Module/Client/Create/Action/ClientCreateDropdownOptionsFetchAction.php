<?php

namespace App\Module\Client\Create\Action;

use App\Core\Application\Responder\JsonResponder;
use App\Module\Client\DropdownFinder\Service\ClientDropdownFinder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class ClientCreateDropdownOptionsFetchAction
{
    public function __construct(
        private JsonResponder $jsonResponder,
        private ClientDropdownFinder $clientUtilFinder,
    ) {
    }

    /**
     * Fetch dropdown options for client create form (lazy load).
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
        array $args,
    ): ResponseInterface {
        $dropdownOptions = $this->clientUtilFinder->findClientDropdownValues();

        return $this->jsonResponder->encodeAndAddToResponse($response, $dropdownOptions);
    }
}
