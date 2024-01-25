<?php

namespace App\Application\Action\User\Ajax;

use App\Application\Renderer\JsonEncoder;
use App\Domain\User\Service\UserUpdater;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class UserUpdateAction
{
    public function __construct(
        private JsonEncoder $jsonEncoder,
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
            return $this->jsonEncoder->encodeAndAddToResponse($response, ['status' => 'success', 'data' => null]);
        }

        // If for example values didn't change
        return $this->jsonEncoder->encodeAndAddToResponse(
            $response,
            ['status' => 'warning', 'message' => 'User wasn\'t updated']
        );
    }
}
