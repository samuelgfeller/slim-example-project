<?php

namespace App\Application\Actions\User\Ajax;

use App\Application\Responder\Responder;
use App\Domain\User\Service\UserActivityManager;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ListUserActivityAction
{
    /**
     * The constructor.
     *
     * @param Responder $responder The responder
     * @param UserActivityManager $userActivityManager
     * @param SessionInterface $session
     */
    public function __construct(
        private readonly Responder $responder,
        private readonly UserActivityManager $userActivityManager,
        private readonly SessionInterface $session
    ) {
    }

    /**
     * Client list all and own Action.
     *
     * @param ServerRequestInterface $request The request
     * @param ResponseInterface $response The response
     *
     * @param array $args
     * @return ResponseInterface The response
     * @throws \JsonException
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $userId = $args['user_id'] ?? $this->session->get('user_id');

        $userResultDataArray = $this->userActivityManager->findUserActivityReport($userId);

        return $this->responder->respondWithJson($response, $userResultDataArray);
    }
}