<?php

namespace App\Application\Actions\Auth;

use App\Application\Responder\Responder;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;

final class RegisterCheckEmailAction
{
    protected Responder $responder;

    /**
     * RegisterAction constructor.
     * @param Responder $responder
     */
    public function __construct(Responder $responder) {
        $this->responder = $responder;
    }

    /**
     * @param ServerRequest $request
     * @param Response $response
     * @return Response
     * @throws \Throwable
     */
    public function __invoke(ServerRequest $request, Response $response): Response
    {
        return $this->responder->render($response, 'auth/register-check-email.html.php');
    }
}