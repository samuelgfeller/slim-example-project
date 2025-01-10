<?php

namespace App\Module\User\Update\Action;

use App\Core\Application\Responder\JsonResponder;
use App\Module\User\Update\Service\UserUpdater;
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
        array $args,
    ): ResponseInterface {
        // Key 'user_id' for the user id in the URL is defined in the route definition in routes.php
        $userIdToChange = (int)$args['user_id'];
        $userValuesToChange = (array)$request->getParsedBody();

        // Call service function to update user with the id and values to change
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
