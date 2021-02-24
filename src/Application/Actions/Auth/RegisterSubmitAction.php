<?php

namespace App\Application\Actions\Auth;

use App\Application\Responder\Responder;
use App\Domain\Auth\AuthService;
use App\Domain\Exceptions\InvalidCredentialsException;
use App\Domain\Exceptions\ValidationException;
use App\Domain\Factory\LoggerFactory;
use App\Domain\User\User;
use App\Domain\User\UserService;
use App\Domain\Utility\ArrayReader;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;

final class RegisterSubmitAction
{
    protected UserService $userService;
    protected LoggerInterface $logger;
    protected Responder $responder;
    protected AuthService $authService;
    private SessionInterface $session;


    public function __construct(
        LoggerFactory $logger,
        UserService $userService,
        Responder $responder,
        AuthService $authService,
        SessionInterface $session
    ) {
        $this->userService = $userService;
        $this->logger = $logger->addFileHandler('error.log')->createInstance('auth-register');
        $this->responder = $responder;
        $this->authService = $authService;
        $this->session = $session;
    }

    public function __invoke(ServerRequest $request, Response $response): Response
    {
        $flash = $this->session->getFlash();
        $userData = $request->getParsedBody();

        if (null !== $userData && [] !== $userData) {
            /** If a html form name changes, these changes have to be done in the entities constructor
             * too since these names will be the keys from the ArrayReader */
            // Check that request body syntax is formatted right
            if (count($userData) === 4 && isset(
                    $userData['name'], $userData['email'], $userData['password'], $userData['password2']
                )) {
                // Use Entity instead of DTO to avoid redundancy (slim-api-example/issues/2)
                $user = new User(new ArrayReader($userData));
                try {
                    // Throws exception if it can't
                    $this->userService->createUser($user);
                } catch (ValidationException $exception) {
                    $flash->add('error', $exception->getMessage());
                    return $this->responder->renderOnValidationError(
                        $response,
                        'auth/register.html.php',
                        $exception->getValidationResult()
                    );
                }

                // Not needed to check if $insertId is null as createUser() returns either string or throws error
                $this->logger->info('User "' . $userData['email'] . '" created');

                try {
                    // Log user in (call normal login method as double check before starting session), (user with plain pass)
                    // Throws InvalidCredentialsException if not allowed
                    $userId = $this->authService->GetUserIdIfAllowedToLogin($user);

                    // Clears all session data and regenerates session ID
                    $this->session->regenerateId();

                    $this->session->set('user', $userId);
                    $flash->add('success', 'Successful registration');

                    $this->logger->info('Successful register + login from user "' . $user->getEmail() . '"');

                    return $this->responder->redirectToRouteName($response, 'post-list-all');
                } catch (InvalidCredentialsException $e) {
                    $flash->add('error', 'Automatic login after registration failed!');

                    // Log error
                    $this->logger->notice(
                        'InvalidCredentialsException thrown with message: "' . $e->getMessage(
                        ) . '" user "' . $e->getUserEmail() . '"'
                    );
                    return $this->responder->redirectToRouteName($response, 'login-page');
                }
            }
            $flash->add('error', 'Malformed request body syntax');
            // Prevent to log passwords
            unset($userData['password'], $userData['password2']);
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