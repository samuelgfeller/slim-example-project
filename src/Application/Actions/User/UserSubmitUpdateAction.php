<?php

namespace App\Application\Actions\User;

use App\Application\Responder\Responder;
use App\Application\Validation\MalformedRequestBodyChecker;
use App\Domain\Exceptions\ForbiddenException;
use App\Domain\Exceptions\ValidationException;
use App\Domain\User\Service\UserUpdater;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class UserSubmitUpdateAction
{

    /**
     * The constructor.
     *
     * @param Responder $responder The responder
     * @param UserUpdater $userUpdater
     * @param MalformedRequestBodyChecker $malformedRequestBodyChecker
     */
    public function __construct(
        private readonly Responder $responder,
        private readonly UserUpdater $userUpdater,
        private readonly MalformedRequestBodyChecker $malformedRequestBodyChecker,

    ) {
    }

    /**
     * Action.
     *
     * @param ServerRequestInterface $request The request
     * @param ResponseInterface $response The response
     *
     * @param array $args The routing arguments
     * @return ResponseInterface The response
     * @throws \JsonException
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        // Id in url user_id defined in routes.php
        $userIdToChange = (int)$args['user_id'];
        $userValuesToChange = $request->getParsedBody();
        if ($this->malformedRequestBodyChecker->requestBodyHasValidKeys($userValuesToChange, [], [
            'first_name',
            'surname',
            'email',
            'status',
            'user_role_id',
        ])) {
            try {
                $updated = $this->userUpdater->updateUser($userIdToChange, $userValuesToChange);
            } catch (ValidationException $exception) {
                return $this->responder->respondWithJsonOnValidationError(
                    $exception->getValidationResult(),
                    $response
                );
            } catch (ForbiddenException $fe) {
                return $this->responder->respondWithJson(
                    $response,
                    [
                        'status' => 'error',
                        'message' => 'You can only edit your user info or be an admin to edit others'
                    ],
                    403
                );
            }

            if ($updated) {
                return $this->responder->respondWithJson($response, ['status' => 'success', 'data' => null]);
            }
            // If for example values didn't change
            return $this->responder->respondWithJson(
                $response,
                ['status' => 'warning', 'message' => 'User wasn\'t updated']
            );
        }
    }
}
