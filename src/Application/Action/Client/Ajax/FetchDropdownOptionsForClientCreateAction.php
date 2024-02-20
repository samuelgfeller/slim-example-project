<?php

namespace App\Application\Action\Client\Ajax;

use App\Application\Renderer\JsonEncoder;
use App\Domain\Client\Service\ClientUtilFinder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class FetchDropdownOptionsForClientCreateAction
{
    public function __construct(
        private JsonEncoder $jsonEncoder,
        private ClientUtilFinder $clientUtilFinder,
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
        array $args
    ): ResponseInterface {
        $dropdownOptions = $this->clientUtilFinder->findClientDropdownValues();

        return $this->jsonEncoder->encodeAndAddToResponse($response, $dropdownOptions);
    }
}
