<?php

namespace App\Application\Actions\Client\Ajax;

use App\Application\Responder\Responder;
use App\Domain\Client\Exception\InvalidClientFilterException;
use App\Domain\Client\Service\ClientUtilFinder;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ClientListDropdownOptionsAction
{
    /**
     * The constructor.
     *
     * @param Responder $responder The responder
     * @param ClientUtilFinder $clientUtilFinder
     */
    public function __construct(
        private readonly Responder $responder,
        private readonly ClientUtilFinder $clientUtilFinder,
    ) {
    }

    /**
     * Client list all and own Action.
     *
     * @param ServerRequestInterface $request The request
     * @param ResponseInterface $response The response
     * @param array $args
     *
     * @throws \JsonException
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
            return $this->responder->respondWithJson(
                $response,
                // Response format tested in PostFilterProvider.php
                [
                    'status' => 'error',
                    'message' => $invalidClientFilterException->getMessage(),
                ],
                StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY
            );
        }

        return $this->responder->respondWithJson($response, $dropdownOptions);
    }
}
