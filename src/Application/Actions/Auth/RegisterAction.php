<?php

namespace App\Application\Actions\Auth;

use App\Application\Responder\Responder;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;

final class RegisterAction
{
    protected Responder $responder;

    /**
     * RegisterAction constructor.
     * @param Responder $responder
     */
    public function __construct(Responder $responder)
    {
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
        return $this->responder->render(
            $response,
            'auth/register.html.php',
            // Provide same query params passed to register page to be added to the register submit request
            ['queryParams' => $request->getQueryParams()]
        );
    }
}