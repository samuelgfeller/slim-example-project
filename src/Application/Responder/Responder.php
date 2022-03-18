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
     *
     * @return ResponseInterface The response
     * @throws \Throwable
     */
    public function render(
        ResponseInterface $response,
        string $template,
        array $data = []
    ): ResponseInterface {
        return $this->phpRenderer->render($response, $template, $data);
    }

    /**
     * Add global variable accessible in templates
     *
     * @param string $key
     * @param $value
     *
     * @return void
     */
    public function addPhpViewAttribute(string $key, $value): void
    {
        $this->phpRenderer->addAttribute($key, $value);
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

    /**
     * Build the path for a named route including the base path
     *
     * @param string $routeName Route name
     * @param string[] $data Named argument replacement data
     * @param string[] $queryParams Optional query string parameters
     *
     * @return string
     */
    public function urlFor(string $routeName, array $data = [], array $queryParams = []): string
    {
        return $this->routeParser->urlFor($routeName, $data, $queryParams);
    }

    /**
     * Render template with validation errors
     *
     * @param ResponseInterface $response
     * @param string $template
     * @param ValidationResult $validationResult
     * @param array $queryParams same query params passed to page to be added again to form after validation error
     * @return ResponseInterface|null
     * @throws \Throwable
     */
    public function renderOnValidationError(
        ResponseInterface $response,
        string $template,
        ValidationResult $validationResult,
        array $queryParams = []
    ): ?ResponseInterface {
        // Add the validation errors to phpRender attributes
        $this->phpRenderer->addAttribute('validation', $validationResult->toArray());
        $this->phpRenderer->addAttribute('formError', true);
        // Provide same query params passed to page to be added again after validation error (e.g. redirect)
        $this->phpRenderer->addAttribute('queryParams', $queryParams);

        // Render template with status code
        return $this->render($response->withStatus($validationResult->getStatusCode()), $template);
    }

    /**
     * Respond with delay user has to wait or action that needs to be made before repeating the action
     *
     * @param ResponseInterface $response
     * @param int|string $remainingDelay
     * @param string $template
     * @param array|null $preloadValues
     * @param array $queryParams same query params passed to page to be added again to form after validation error
     * @return ResponseInterface
     * @throws \Throwable
     */
    public function respondWithThrottle(
        ResponseInterface $response,
        int|string $remainingDelay,
        string $template,
        array $preloadValues = null,
        array $queryParams = []
    ): ResponseInterface {
        $this->phpRenderer->addAttribute('throttleDelay', $remainingDelay);
        $this->phpRenderer->addAttribute('preloadValues', $preloadValues);
        $this->phpRenderer->addAttribute('formError', true);
        // Provide same query params passed to page to be added again after validation error (e.g. redirect)
        $this->phpRenderer->addAttribute('queryParams', $queryParams);

        return $this->render($response->withStatus(422), $template);
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
            'data' => $validationResult->toArray(),
        ];
        return $this->respondWithJson($response, $responseData, $validationResult->getStatusCode());
    }
}
