<?php

/**
 * Action because it is used by many different modules
 * and Controller.php is an abstract class.
 */

namespace App\Application\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class PreflightAction
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        // Do nothing here. Just return the response.
        return $response;
    }
}
