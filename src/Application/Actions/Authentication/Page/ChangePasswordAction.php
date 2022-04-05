<?php

namespace App\Application\Actions\Authentication\Page;

use App\Application\Responder\Responder;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;

/**
 * For user that wants to change its password while being authenticated
 */
class ChangePasswordAction
{

    /**
     * The constructor.
     *
     * @param Responder $responder
     */
    public function __construct(
        private Responder $responder,
    ) {
    }

    /**
     * Check if token is valid and if yes display password form
     *
     * @param ServerRequest $request
     * @param Response $response
     * @return Response
     * @throws \Throwable
     */
    public function __invoke(ServerRequest $request, Response $response): Response
    {
        return $this->responder->render($response, 'authentication/change-password.html.php');
    }
}