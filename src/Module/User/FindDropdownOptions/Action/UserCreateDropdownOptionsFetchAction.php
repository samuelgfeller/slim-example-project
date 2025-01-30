<?php

namespace App\Module\User\FindDropdownOptions\Action;

use App\Application\Responder\JsonResponder;
use App\Module\User\FindDropdownOptions\Service\UserDropdownOptionFinder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class UserCreateDropdownOptionsFetchAction
{
    public function __construct(
        private JsonResponder $jsonResponder,
        private UserDropdownOptionFinder $userUtilFinder,
    ) {
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args,
    ): ResponseInterface {
        $dropdownOptions = $this->userUtilFinder->findUserDropdownValues();

        return $this->jsonResponder->encodeAndAddToResponse($response, $dropdownOptions);
    }
}
