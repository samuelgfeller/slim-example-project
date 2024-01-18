<?php

namespace App\Application\Action\Client\Ajax;

use App\Application\Renderer\JsonEncoder;
use App\Domain\Client\Exception\InvalidClientFilterException;
use App\Domain\Client\Service\ClientUtilFinder;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

readonly class FetchDropdownOptionsForClientCreateAction
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
        try {
            $dropdownOptions = $this->clientUtilFinder->findClientDropdownValues();
        } catch (InvalidClientFilterException $invalidClientFilterException) {
            return $this->jsonEncoder->encodeAndAddToResponse(
                $response,
                // Response format tested in PostFilterProvider.php
                [
                    'status' => 'error',
                    'message' => $invalidClientFilterException->getMessage(),
                ],
                StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY
            );
        }

        return $this->jsonEncoder->encodeAndAddToResponse($response, $dropdownOptions);
    }
}
