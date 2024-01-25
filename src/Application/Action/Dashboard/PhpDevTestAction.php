<?php

namespace App\Application\Action\Dashboard;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * This action serves when I want to test php concepts, syntax or else while developing.
 */
class PhpDevTestAction
{
    public function __construct(
    ) {
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        return $response;
    }
}
