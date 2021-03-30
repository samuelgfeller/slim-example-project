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
use App\Domain\Utility\ArrayReader;
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
            // ? (and if isset condition below) too since these names will be the keys from the ArrayReader
            // Check that request body syntax is formatted right
            if (count($userData) === 2 && isset($userData['email'], $userData['password'])) {
                // Use Entity instead of DTO to avoid redundancy (slim-api-example/issues/2)
                $user = new User(new ArrayReader($userData));
                try {
                    // Throws InvalidCredentialsException if not allowed
                    $userId = $this->authService->GetUserIdIfAllowedToLogin($user);

                    // Clear all session data and regenerate session ID
                    $this->session->regenerateId();
                    // Add user to session
                    $this->session->set('user', $userId);

                    // Add success message to flash
                    $flash->add('success', 'Login successful');

                    $this->logger->info('Successful login from user "' . $user->getEmail() . '"');

                    return $this->responder->redirectToRouteName($response, 'hello');
                } catch (ValidationException $exception) {
                    $flash->add('error', $exception->getMessage());
                    return $this->responder->renderOnValidationError(
                        $response,
                        'auth/login.html.php',
                        $exception->getValidationResult()
                    );
                } catch (InvalidCredentialsException $e) {
                    $flash->add('error', 'Invalid credentials.');

                    // Log error
                    $this->logger->notice(
                        'InvalidCredentialsException thrown with message: "' . $e->getMessage() . '" user "' . $e->getUserEmail(
                        ) . '"'
                    );

                    return $this->responder->redirectToRouteName($response, 'login-page');
                } catch (SecurityException $se){
                    // todo inform user that they have to wait or fill out captcha
                    throw $se;
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