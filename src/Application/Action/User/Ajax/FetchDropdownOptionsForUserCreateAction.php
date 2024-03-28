<?php

namespace App\Application\Action\User\Ajax;

use App\Application\Responder\JsonEncoder;
use App\Domain\User\Service\UserUtilFinder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class FetchDropdownOptionsForUserCreateAction
{
    public function __construct(
        private JsonEncoder $jsonEncoder,
        private UserUtilFinder $userUtilFinder,
    ) {
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $dropdownOptions = $this->userUtilFinder->findUserDropdownValues();

        return $this->jsonEncoder->encodeAndAddToResponse($response, $dropdownOptions);
    }
}
