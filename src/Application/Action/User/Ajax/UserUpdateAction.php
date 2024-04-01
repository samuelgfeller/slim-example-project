<?php

namespace App\Application\Action\User\Ajax;

use App\Application\Responder\JsonResponder;
use App\Domain\User\Service\UserUpdater;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class UserUpdateAction
{
    public function __construct(
        private JsonResponder $jsonResponder,
        private UserUpdater $userUpdater,
    ) {
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        // Id in url user_id defined in routes.php
        $userIdToChange = (int)$args['user_id'];
        $userValuesToChange = (array)$request->getParsedBody();
        $updated = $this->userUpdater->updateUser($userIdToChange, $userValuesToChange);

        if ($updated) {
            return $this->jsonResponder->encodeAndAddToResponse($response, ['status' => 'success', 'data' => null]);
        }

        // If for example values didn't change
        return $this->jsonResponder->encodeAndAddToResponse(
            $response,
            ['status' => 'warning', 'message' => 'User wasn\'t updated']
        );
    }
}
