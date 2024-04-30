<?php

namespace App\Application\Action\User\Ajax;

use App\Application\Responder\JsonResponder;
use App\Domain\User\Service\UserUtilFinder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class UserCreateDropdownOptionsFetchAction
{
    public function __construct(
        private JsonResponder $jsonResponder,
        private UserUtilFinder $userUtilFinder,
    ) {
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $dropdownOptions = $this->userUtilFinder->findUserDropdownValues();

        return $this->jsonResponder->encodeAndAddToResponse($response, $dropdownOptions);
    }
}
