<?php

namespace App\Core\Application\Responder;

use App\Modules\Exception\Domain\ValidationException;
use App\Modules\Security\Domain\Exception\SecurityException;
use Psr\Http\Message\ResponseInterface;
use Slim\Views\PhpRenderer;

final readonly class TemplateRenderer
{
    public function __construct(private PhpRenderer $phpRenderer)
    {
    }

    /**
     * Render template.
     *
     * @param ResponseInterface $response The response
     * @param string $template Template pathname relative to templates directory
     * @param array $data Associative array of template variables
     *
     * @return ResponseInterface The response
     */
    public function render(ResponseInterface $response, string $template, array $data = []): ResponseInterface
    {
        return $this->phpRenderer->render($response, $template, $data);
    }

    /**
     * Add global variable accessible in templates.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return void
     */
    public function addPhpViewAttribute(string $key, mixed $value): void
    {
        $this->phpRenderer->addAttribute($key, $value);
    }

    /**
     * Render template with validation errors.
     *
     * @param ResponseInterface $response
     * @param string $template
     * @param ValidationException $validationException
     * @param array $queryParams same query params passed to page to be added again to form after validation error
     * @param array|null $preloadValues
     *
     * @return ResponseInterface
     */
    public function renderOnValidationError(
        ResponseInterface $response,
        string $template,
        ValidationException $validationException,
        array $queryParams = [],
        ?array $preloadValues = null,
    ): ResponseInterface {
        $this->phpRenderer->addAttribute('preloadValues', $preloadValues);

        // Add the validation errors to phpRender attributes
        $this->phpRenderer->addAttribute('validation', $validationException->validationErrors);
        $this->phpRenderer->addAttribute('formError', true);
        // Provide same query params passed to page to be added again after validation error (e.g. redirect)
        $this->phpRenderer->addAttribute('queryParams', $queryParams);

        // Render template with status code
        return $this->render($response->withStatus(422), $template);
    }

    /**
     * Respond with delay user has to wait or action that needs to be made before repeating the action.
     * Specifically for form errors.
     *
     * @param ResponseInterface $response
     * @param string $template
     * @param SecurityException $securityException
     * @param array|null $preloadValues
     * @param array $queryParams same query params passed to page to be added again to form after validation error
     *
     * @throws \Throwable
     *
     * @return ResponseInterface
     */
    public function respondWithFormThrottle(
        ResponseInterface $response,
        string $template,
        SecurityException $securityException,
        array $queryParams = [],
        ?array $preloadValues = null,
    ): ResponseInterface {
        $this->phpRenderer->addAttribute('throttleDelay', $securityException->getRemainingDelay());
        $this->phpRenderer->addAttribute('formErrorMessage', $securityException->getPublicMessage());
        $this->phpRenderer->addAttribute('preloadValues', $preloadValues);
        $this->phpRenderer->addAttribute('formError', true);

        // Provide same query params passed to page to be added again after validation error (e.g. redirect)
        $this->phpRenderer->addAttribute('queryParams', $queryParams);

        return $this->render($response->withStatus(422), $template);
    }
}
