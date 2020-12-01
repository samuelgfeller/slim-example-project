<?php

namespace App\Application\Responder;

use App\Application\Responder\UrlGenerator;
use App\Domain\Validation\ValidationResult;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Views\Twig;

/**
 * A generic responder.
 */
final class Responder
{

    private UrlGenerator $urlGenerator;

    private ResponseFactoryInterface $responseFactory;

    private Twig $twig;

    /**
     * The constructor.
     *
     * @param UrlGenerator $urlGenerator The url generator
     * @param ResponseFactoryInterface $responseFactory The response factory
     * @param Twig $twig Twig engine
     */
    public function __construct(
        UrlGenerator $urlGenerator,
        ResponseFactoryInterface $responseFactory,
        Twig $twig
    ) {
        $this->urlGenerator = $urlGenerator;
        $this->responseFactory = $responseFactory;
        $this->twig = $twig;
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
     *
     * @return ResponseInterface The response
     */
//    todo in SLE-23
    public function render(ResponseInterface $response, string $template, array $data = []): ResponseInterface
    {
        return $this->twig->render($response, $template, $data);
    }

    /**
     * Creates a redirect for the given url / route name.
     *
     * This method prepares the response object to return an HTTP Redirect
     * response to the client.
     *
     * @param ResponseInterface $response The response
     * @param string $destination The redirect destination (url or route name)
     * @param array<mixed> $data Named argument replacement data
     * @param array<mixed> $queryParams Optional query string parameters
     *
     * @return ResponseInterface The response
     */
    public function redirect(
        ResponseInterface $response,
        string $destination,
        array $data = [],
        array $queryParams = []
    ): ResponseInterface {
        if (!filter_var($destination, FILTER_VALIDATE_URL)) {
            $destination = $this->urlGenerator->fullUrlFor($destination, $data, $queryParams);
        }

        return $response->withStatus(302)->withHeader('Location', $destination);
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
     * @throws \JsonException
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

    public function respondWithJsonValidationError(ValidationResult $validationResult, ResponseInterface $response): ?ResponseInterface
    {
        $responseData = [
            'status' => 'error',
            'message' => 'Validation error',
            'validation' => $validationResult->toArray(),
        ];
        return $this->respondWithJson($response, $responseData, $validationResult->getStatusCode());
    }
}