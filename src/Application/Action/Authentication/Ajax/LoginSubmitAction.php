<?php

namespace App\Application\Action\Authentication\Ajax;

use App\Application\Responder\RedirectHandler;
use App\Application\Responder\TemplateRenderer;
use App\Domain\Authentication\Exception\InvalidCredentialsException;
use App\Domain\Authentication\Exception\UnableToLoginStatusNotActiveException;
use App\Domain\Authentication\Service\LoginVerifier;
use App\Domain\Exception\ValidationException;
use App\Domain\Security\Exception\SecurityException;
use App\Domain\User\Service\UserFinder;
use Odan\Session\SessionInterface;
use Odan\Session\SessionManagerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final readonly class LoginSubmitAction
{
    public function __construct(
        private RedirectHandler $redirectHandler,
        private TemplateRenderer $templateRenderer,
        private LoggerInterface $logger,
        private LoginVerifier $loginVerifier,
        private SessionManagerInterface $sessionManager,
        private SessionInterface $session,
        private UserFinder $userFinder,
    ) {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $flash = $this->session->getFlash();
        $submitValues = (array)$request->getParsedBody();
        $queryParams = $request->getQueryParams();

        try {
            // Throws InvalidCredentialsException if not allowed
            $userId = $this->loginVerifier->verifyLoginAndGetUserId($submitValues, $queryParams);

            // Regenerate session ID
            $this->sessionManager->regenerateId();
            // Add user to session
            $this->session->set('user_id', $userId);

            // Add success message to flash
            $flash->add('success', __('Login successful'));

            // Check if user has enabled dark mode and if yes populate var
            $themeQueryParams = [];
            if ($theme = $this->userFinder->findUserById($userId)->theme) {
                $themeQueryParams['theme'] = $theme->value;
            }

            // After register and login success, check if user should be redirected
            if (isset($queryParams['redirect'])) {
                return $this->redirectHandler->redirectToUrl(
                    $response,
                    $request->getQueryParams()['redirect'],
                    $themeQueryParams
                );
            }

            return $this->redirectHandler->redirectToRouteName($response, 'home-page', [], $themeQueryParams);
        } // When the response is not JSON but rendered, the validation exception has to be caught in action
        catch (ValidationException $ve) {
            return $this->templateRenderer->renderOnValidationError(
                $response,
                'authentication/login.html.php',
                $ve,
                $queryParams
            );
        } catch (InvalidCredentialsException $invalidCredentialsException) {
            // Log error
            $this->logger->notice(
                'InvalidCredentialsException thrown with message: "' . $invalidCredentialsException->getMessage() .
                '" user "' . $invalidCredentialsException->getUserEmail() . '"'
            );
            $this->templateRenderer->addPhpViewAttribute('formError', true);
            $this->templateRenderer->addPhpViewAttribute(
                'formErrorMessage',
                __('Invalid credentials. Please try again.')
            );
            if (!empty($submitValues['email'])) {
                $this->templateRenderer->addPhpViewAttribute('preloadValues', ['email' => $submitValues['email']]);
            }

            return $this->templateRenderer->render(
                $response->withStatus(401),
                'authentication/login.html.php',
                // Provide same query params passed to login page to be added to the login submit request
                ['queryParams' => $request->getQueryParams()]
            );
        } catch (SecurityException $securityException) {
            if (PHP_SAPI === 'cli') {
                // If script is called from commandline (e.g. testing) throw error instead of rendering page
                throw $securityException;
            }

            return $this->templateRenderer->respondWithFormThrottle(
                $response,
                'authentication/login.html.php',
                $securityException,
                $request->getQueryParams(),
                ['email' => $submitValues['email']],
            );
        } catch (UnableToLoginStatusNotActiveException $unableToLoginException) {
            // When user status is not "active"
            $this->templateRenderer->addPhpViewAttribute('formError', true);
            // Add form error message
            $this->templateRenderer->addPhpViewAttribute('formErrorMessage', $unableToLoginException->getMessage());

            return $this->templateRenderer->render(
                $response->withStatus(401),
                'authentication/login.html.php',
                // Provide the same query params passed to login page to be added to the login submit request
                ['queryParams' => $request->getQueryParams()]
            );
        }
    }
}
