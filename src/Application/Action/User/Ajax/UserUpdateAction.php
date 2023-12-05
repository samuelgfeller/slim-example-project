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

    /**
     * Action.
     *
     * @param ServerRequestInterface $request The request
     * @param ResponseInterface $response The response
     * @param array $args The routing arguments
     *
     * @return ResponseInterface The response
     */
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
            return $this->jsonResponder->respondWithJson($response, ['status' => 'success', 'data' => null]);
        }

        // If for example values didn't change
        return $this->jsonResponder->respondWithJson(
            $response,
            ['status' => 'warning', 'message' => 'User wasn\'t updated']
        );
    }
}
