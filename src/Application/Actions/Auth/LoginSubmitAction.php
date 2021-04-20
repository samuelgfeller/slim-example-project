<?php

namespace App\Application\Actions\Auth;

use App\Application\Responder\Responder;
use App\Domain\Auth\AuthService;
use App\Domain\Exceptions\InvalidCredentialsException;
use App\Domain\Exceptions\ValidationException;
use App\Domain\Factory\LoggerFactory;
use App\Domain\Security\SecurityException;
use App\Domain\Security\SecurityService;
use App\Domain\User\User;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;

final class LoginSubmitAction
{
    protected LoggerInterface $logger;

    public function __construct(
        protected Responder $responder,
        LoggerFactory $logger,
        private AuthService $authService,
        private SecurityService $securityService,
        private SessionInterface $session
    ) {
        $this->logger = $logger->addFileHandler('error.log')->createInstance('auth-login');
    }

    public function __invoke(ServerRequest $request, Response $response): Response
    {
        $flash = $this->session->getFlash();
        $userData = $request->getParsedBody();

        if (null !== $userData && [] !== $userData) {
            // ? If a html form name changes, these changes have to be done in the entities constructor
            // ? (and if isset condition below) too since these names will be the keys of the ArrayReader
            // Check that request body syntax is formatted right (3 when with captcha)
            $requiredAreSet = isset($userData['email'], $userData['password']);
            if (
                ($requiredAreSet && count($userData) === 2) ||
                ($requiredAreSet && (count($userData) === 3 && isset($userData['g-recaptcha-response'])))
            ) {
                // Populate $captcha var if reCAPTCHA response is given
                $captcha = $userData['g-recaptcha-response'] ?? null;

                // Use Entity instead of DTO to avoid redundancy (slim-api-example/issues/2)
                $user = new User($userData);
                try {
                    // Throws InvalidCredentialsException if not allowed
                    $userId = $this->authService->GetUserIdIfAllowedToLogin($user, $captcha);

                    // Clear all session data and regenerate session ID
                    $this->session->regenerateId();
                    // Add user to session
                    $this->session->set('user', $userId);

                    // Add success message to flash
                    $flash->add('success', 'Login successful');

                    $this->logger->info('Successful login from user "' . $user->getEmail() . '"');

                    return $this->responder->redirectToRouteName($response, 'hello');
                } catch (ValidationException $ve) {
                    $flash->add('error', $ve->getMessage());
                    return $this->responder->renderOnValidationError(
                        $response,
                        'auth/login.html.php',
                        $ve->getValidationResult()
                    );
                } catch (InvalidCredentialsException $e) {
                    $flash->add('error', 'Invalid credentials.');
                    // Log error
                    $this->logger->notice(
                        'InvalidCredentialsException thrown with message: "' . $e->getMessage() . '" user "' .
                        $e->getUserEmail() . '"'
                    );
                    $this->responder->addAttribute('formError', true);
                    return $this->responder->render($response->withStatus(401), 'auth/login.html.php');
                } catch (SecurityException $se) {
                    if (PHP_SAPI === 'cli') {
                        // If script is called from commandline (e.g. testing) throw error instead of rendering page
                        throw $se;
                    }
                    $flash->add('error', $se->getPublicMessage());
                    return $this->responder->respondWithThrottle(
                        $response,
                        $se->getRemainingDelay(),
                        'auth/login.html.php',
                        ['email' => $user->getEmail()]
                    );
                }
            }
            $flash->add('error', 'Malformed request body syntax');
            // Prevent to log passwords
            unset($userData['password']);
            $this->logger->error('POST request body malformed: ' . json_encode($userData));
            // Caught in error handler which displays error page because if POST request body is empty frontend has error
            // Error message same as in tests/Provider/UserProvider->malformedRequestBodyProvider()
            throw new HttpBadRequestException($request, 'Request body malformed.');
        }
        $flash->add('error', 'Request body empty');
        $this->logger->error('POST request body empty');
        // Caught in error handler which displays error page because if POST request body is empty frontend has error
        // Error message same as in tests/Provider/UserProvider->malformedRequestBodyProvider()
        throw new HttpBadRequestException($request, 'Request body is empty.');
    }
}