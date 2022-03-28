<?php

namespace App\Application\Actions\Authentication;

use App\Application\Responder\Responder;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;

class PasswordForgottenAction
{
    /**
     * The constructor.
     *
     * @param Responder $responder
     */
    public function __construct(
        private Responder $responder
    ) {
    }

    /**
     * @param ServerRequest $request
     * @param Response $response
     * @return Response
     * @throws \Throwable
     */
    public function __invoke(ServerRequest $request, Response $response): Response
    {
        return $this->responder->render(
            $response,
            'authentication/password-forgotten.html.php'
        );
    }
}