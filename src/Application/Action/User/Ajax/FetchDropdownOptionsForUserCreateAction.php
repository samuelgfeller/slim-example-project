<?php

namespace App\Application\Action\User\Ajax;

use App\Application\Responder\JsonResponder;
use App\Domain\User\Service\UserUtilFinder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class FetchDropdownOptionsForUserCreateAction
{
    public function __construct(
        private readonly JsonResponder $jsonResponder,
        private readonly UserUtilFinder $userUtilFinder,
    ) {
    }

    /**
     * Action.
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
        $dropdownOptions = $this->userUtilFinder->findUserDropdownValues();

        return $this->jsonResponder->respondWithJson($response, $dropdownOptions);
    }
}
