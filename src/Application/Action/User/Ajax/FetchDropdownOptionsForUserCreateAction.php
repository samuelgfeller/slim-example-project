<?php

namespace App\Application\Action\User\Ajax;

use App\Application\Renderer\JsonEncoder;
use App\Domain\User\Service\UserUtilFinder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

readonly class FetchDropdownOptionsForUserCreateAction
{
    public function __construct(
        private JsonEncoder $jsonEncoder,
        private UserUtilFinder $userUtilFinder,
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

        return $this->jsonEncoder->encodeAndAddToResponse($response, $dropdownOptions);
    }
}
