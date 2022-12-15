<?php

namespace App\Application\Actions\Dashboard;

use App\Application\Responder\Responder;
use App\Application\Validation\MalformedRequestBodyChecker;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * This action serves when I want to test php concepts, syntax or else while developing.
 */
class PhpDevTestAction
{
    public function __construct(
        private readonly Responder $responder,
        private readonly MalformedRequestBodyChecker $requestBodyKeysValidator
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
        $parsedBody = [
            'client_status_id' => 2,
            'sex2' => 2,
        ];
        $requiredKeys = [
            'client_status_id',
            'sex',
        ];
        $optionalKeys = [
            'sex2',
        ];
        $success = false;

        // var_dump($this->requestBodyKeysValidator->requestBodyHasValidKeys($parsedBody, $requiredKeys, $optionalKeys));

        return $this->responder->createResponse($response, ['success' => $success]);
    }
}
