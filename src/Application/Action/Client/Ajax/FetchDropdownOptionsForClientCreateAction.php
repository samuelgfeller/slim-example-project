<?php

namespace App\Application\Action\Client\Ajax;

use App\Application\Responder\JsonResponder;
use App\Domain\Client\Exception\InvalidClientFilterException;
use App\Domain\Client\Service\ClientUtilFinder;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class FetchDropdownOptionsForClientCreateAction
{
    public function __construct(
        private readonly JsonResponder $jsonResponder,
        private readonly ClientUtilFinder $clientUtilFinder,
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
            return $this->jsonResponder->respondWithJson(
                $response,
                // Response format tested in PostFilterProvider.php
                [
                    'status' => 'error',
                    'message' => $invalidClientFilterException->getMessage(),
                ],
                StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY
            );
        }

        return $this->jsonResponder->respondWithJson($response, $dropdownOptions);
    }
}
