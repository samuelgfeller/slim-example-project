<?php

declare(strict_types=1);

namespace App\Application\Controllers;

use App\Domain\Validation\ValidationResult;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;

abstract class Controller
{
    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param Response $response
     * @param array|object|null $data
     * @param int $status
     * @return Response
     */
    protected function respondWithJson(Response $response, $data = null, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data));
        $response = $response->withStatus($status);
        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * @param Response $response
     * @param array|object|null $data
     * @return Response
     */
    protected function respondWithPrettyJson(Response $response, $data = null): Response
    {
        $json = json_encode($data, JSON_PRETTY_PRINT);
        $response->getBody()->write($json);
        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * If the user_id is in the JWT Token data it is returned
     *
     * @param Request $request
     * @return int|null
     */
    protected function getUserIdFromToken(Request $request): ?int
    {
        if (isset($request->getAttribute('token')['data'])) {
            // token 'data' is an stdClass and can be transformed into array with this function https://stackoverflow.com/a/18576902/9013718
            return (int)json_decode(json_encode($request->getAttribute('token')['data']), true)['userId'];
        }
        return null;
    }

    protected function respondValidationError(ValidationResult $validationResult, Response $response): ?Response
    {
        $responseData = [
            'status' => 'error',
            'message' => 'Validation error',
            'validation' => $validationResult->toArray(),
        ];
        return $this->respondWithJson($response, $responseData, $validationResult->getStatusCode());
    }

}
