<?php

namespace App\Application\Actions\Authentication\Submit;

use App\Application\Responder\Responder;
use App\Application\Validation\MalformedRequestBodyChecker;
use App\Domain\Authentication\Exception\InvalidCredentialsException;
use App\Domain\Authentication\Exception\UnableToLoginStatusNotActiveException;
use App\Domain\Authentication\Service\LoginVerifier;
use App\Domain\Factory\LoggerFactory;
use App\Domain\Security\Exception\SecurityException;
use App\Domain\Validation\ValidationException;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;

final class LoginSubmitAction
{
    protected LoggerInterface $logger;

    public function __construct(
        private readonly Responder $responder,
        LoggerFactory $logger,
        private readonly LoginVerifier $loginVerifier,
        private readonly SessionInterface $session,
        private readonly MalformedRequestBodyChecker $malformedRequestBodyChecker,
    ) {
        $this->logger = $logger->addFileHandler('error.log')->createInstance('auth-login');
    }

    public function __invoke(ServerRequest $request, Response $response): Response
    {
        $flash = $this->session->getFlash();
        $submitValues = $request->getParsedBody();
        $queryParams = $request->getQueryParams();

        if ($this->malformedRequestBodyChecker->requestBodyHasValidKeys(
            $submitValues,
            ['email', 'password'],
            ['g-recaptcha-response']
        )) {
            try {
                // Throws InvalidCredentialsException if not allowed
                $userId = $this->loginVerifier->getUserIdIfAllowedToLogin(
                    $submitValues,
                    $submitValues['g-recaptcha-response'] ?? null,
                    $queryParams
                );

                // Clear all session data and regenerate session ID
                $this->session->regenerateId();
                // Add user to session
                $this->session->set('user_id', $userId);

                // Add success message to flash
                $flash->add('success', 'Login successful');

                // After register and login success, check if user should be redirected
                if (isset($queryParams['redirect'])) {
                    return $this->responder->redirectToUrl($response, $request->getQueryParams()['redirect']);
                }

                return $this->responder->redirectToRouteName($response, 'home-page');
            } catch (ValidationException $ve) {
                return $this->responder->renderOnValidationError(
                    $response,
                    'authentication/login.html.php',
                    $ve,
                    $queryParams
                );
            } catch (InvalidCredentialsException $e) {
                // Log error
                $this->logger->notice(
                    'InvalidCredentialsException thrown with message: "' . $e->getMessage() . '" user "' .
                    $e->getUserEmail() . '"'
                );
                $this->responder->addPhpViewAttribute('formError', true);
                $this->responder->addPhpViewAttribute('formErrorMessage', 'Invalid credentials. Please try again.');

                return $this->responder->render(
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

                return $this->responder->respondWithFormThrottle(
                    $response,
                    'authentication/login.html.php',
                    $securityException,
                    $request->getQueryParams(),
                    ['email' => $submitValues['email']],
                );
            } catch (UnableToLoginStatusNotActiveException $unableToLoginException) {
                // When user doesn't have status active
                $this->responder->addPhpViewAttribute('formError', true);
                // Add form error message
                $this->responder->addPhpViewAttribute('formErrorMessage', $unableToLoginException->getMessage());

                return $this->responder->render(
                    $response->withStatus(401),
                    'authentication/login.html.php',
                    // Provide same query params passed to login page to be added to the login submit request
                    ['queryParams' => $request->getQueryParams()]
                );
            }
        }
        $flash->add('error', 'Malformed request body syntax');
        // Prevent to log passwords
        unset($submitValues['password']);
        $this->logger->error('POST request body malformed: ' . json_encode($submitValues));
        // Caught in error handler which displays error page because if POST request body is empty frontend has error
        // Error message same as in tests/Provider/UserProvider->malformedRequestBodyProvider()
        throw new HttpBadRequestException($request, 'Request body malformed.');
    }
}
