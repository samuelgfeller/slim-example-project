<?php

namespace App\Application\Actions\Authentication\Page;

use App\Application\Responder\Responder;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;

final class RegisterAction
{

    /**
     * RegisterAction constructor.
     * @param Responder $responder
     */
    public function __construct(
        private Responder $responder)
    {
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
            'authentication/register.html.php',
            // Provide same query params passed to register page to be added to the register submit request
            ['queryParams' => $request->getQueryParams()]
        );
    }
}