<?php

namespace App\Application\Responder;

use App\Domain\Validation\ValidationResult;
use JsonException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Interfaces\RouteParserInterface;
use Slim\Views\PhpRenderer;
use function http_build_query;

/**
 * A generic responder.
 */
final class Responder
{

    private RouteParserInterface $routeParser;

    private ResponseFactoryInterface $responseFactory;

    private PhpRenderer $phpRenderer;

    /**
     * The constructor.
     *
     * @param RouteParserInterface $routeParser The route parser
     * @param ResponseFactoryInterface $responseFactory The response factory
     * @param PhpRenderer $phpRenderer slimphp/PHP-View renderer
     */
    public function __construct(
        RouteParserInterface $routeParser,
        ResponseFactoryInterface $responseFactory,
        PhpRenderer $phpRenderer
    ) {
        $this->routeParser = $routeParser;
        $this->responseFactory = $responseFactory;
        $this->phpRenderer = $phpRenderer;
    }

    /**
     * Create a new response.
     *
     * @return ResponseInterface The response
     */
    public function createResponse(): ResponseInterface
    {
        return $this->responseFactory->createResponse()->withHeader('Content-Type', 'text/html; charset=utf-8');
    }

    /**
     * Output rendered template.
     *
     * @param ResponseInterface $response The response
     * @param string $template Template pathname relative to templates directory
     * @param array $data Associative array of template variables
     * @param string $layout optional layout path default: layout.html.php
     *
     * @return ResponseInterface The response
     * @throws \Throwable
     */
    public function render(
        ResponseInterface $response,
        string $template,
        array $data = [],
        string $layout = 'layout.html.php'
    ): ResponseInterface {
        $this->phpRenderer->setLayout($layout);
        return $this->phpRenderer->render($response, $template, $data);
    }

    /**
     * Creates a redirect for the given url
     *
     * This method prepares the response object to return an HTTP Redirect
     * response to the client.
     *
     * @param ResponseInterface $response The response
     * @param string $destination The redirect destination (url or route name)
     * @param array<mixed> $queryParams Optional query string parameters
     *
     * @return ResponseInterface The response
     */
    public function redirectToUrl(
        ResponseInterface $response,
        string $destination,
        array $queryParams = []
    ): ResponseInterface {
        if ($queryParams) {
            $destination = sprintf('%s?%s', $destination, http_build_query($queryParams));
        }

        return $response->withStatus(302)->withHeader('Location', $destination);
    }

    /**
     * Creates a redirect for the given route name.
     *
     * This method prepares the response object to return an HTTP Redirect
     * response to the client.
     *
     * @param ResponseInterface $response The response
     * @param string $routeName The redirect route name
     * @param array<mixed> $data Named argument replacement data
     * @param array<mixed> $queryParams Optional query string parameters
     *
     * @return ResponseInterface The response
     */
    public function redirectToRouteName(
        ResponseInterface $response,
        string $routeName,
        array $data = [],
        array $queryParams = []
    ): ResponseInterface {
        return $this->redirectToUrl($response, $this->routeParser->urlFor($routeName, $data, $queryParams));
    }



    /* Below is temp @todo remove */

    public function redirectForOnValidationError(
        ResponseInterface $response,
        ValidationResult $validationResult,
        string $destination
    ): ?ResponseInterface {
        $responseData = [
            'status' => 'error',
            'message' => 'Validation error',
            'validation' => $validationResult->toArray(),
        ];
//        $flash->add()
        return $this->redirectToRouteName($response, $destination);
    }

    /**
     * Write JSON to the response body.
     *
     * This method prepares the response object to return an HTTP JSON
     * response to the client.
     *
     * @param ResponseInterface $response The response
     * @param mixed $data The data
     * @param int $status
     * @return ResponseInterface The response
     * @throws JsonException
     */
    public function respondWithJson(
        ResponseInterface $response,
        $data = null,
        int $status = 200
    ): ResponseInterface {
        $response->getBody()->write((string)json_encode($data, JSON_THROW_ON_ERROR));
        $response = $response->withStatus($status);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function respondWithJsonOnValidationError(
        ValidationResult $validationResult,
        ResponseInterface $response
    ): ?ResponseInterface {
        $responseData = [
            'status' => 'error',
            'message' => 'Validation error',
            'validation' => $validationResult->toArray(),
        ];
        return $this->respondWithJson($response, $responseData, $validationResult->getStatusCode());
    }
}
