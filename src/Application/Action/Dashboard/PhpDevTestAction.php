<?php

namespace App\Application\Action\Dashboard;

use App\Application\Responder\Responder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * This action serves when I want to test php concepts, syntax or else while developing.
 */
class PhpDevTestAction
{
    public function __construct(
        private readonly Responder $responder,
    ) {
    }

    /**
     * Action.
     *
     * @param ServerRequestInterface $request The request
     * @param ResponseInterface $response The response
     * @param array $args
     *
     * @return ResponseInterface The response
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        return $this->responder->createResponse();
    }
}
