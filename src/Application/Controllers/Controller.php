<?php

declare(strict_types=1);

namespace App\Application\Controllers;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;

abstract class Controller
{
    protected $logger;

    public function __construct(LoggerInterface $logger) {
        $this->logger = $logger;
    }

    /**
     * @param Response $response
     * @param array|object|null $data
     * @return Response
     */
    protected function respondWithJson(Response $response, $data = null)
    {
        $response->getBody()->write(json_encode($data));
        $response->withStatus(200);
        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * @param Response $response
     * @param array|object|null $data
     * @return Response
     */
    protected function respondWithDataPrettyJson(Response $response, $data = null): Response
    {
        $json = json_encode($data, JSON_PRETTY_PRINT);
        $response->getBody()->write($json);
        return $response->withHeader('Content-Type', 'application/json');
    }

}
