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
    protected LoggerInterface $logger;


    public function __construct(
        LoggerFactory $logger,
        protected UserService $userService,
        protected Responder $responder,
        protected AuthService $authService,
        private SessionInterface $session
    ) {
        $this->logger = $logger->addFileHandler('error.log')->createInstance('auth-register');
    }

    public function __invoke(ServerRequest $request, Response $response): Response
    {
        $flash = $this->session->getFlash();
        $userData = $request->getParsedBody();

        if (null !== $userData && [] !== $userData) {
            /** If a html form name changes, these changes have to be done in the entities constructor
             * (and if isset condition below) too since these names will be the keys from the ArrayReader */
            // Check that request body syntax is formatted right
            if (count($userData) === 4 && isset(
                    $userData['name'], $userData['email'], $userData['password'], $userData['password2']
                )) {
                // Use Entity instead of DTO to avoid redundancy (slim-api-example/issues/2)
                $user = new User(new ArrayReader($userData));
                try {
                    // Throws exception if there is error and returns false if user already exists
                    $insertId = $this->authService->registerUser($user);
                    $flash->add('success', 'Email has been sent.');
                } catch (ValidationException $exception) {
                    $flash->add('error', $exception->getMessage());
                    return $this->responder->renderOnValidationError(
                        $response,
                        'auth/register.html.php',
                        $exception->getValidationResult()
                    );
                } catch (\PHPMailer\PHPMailer\Exception $e) {
                    $flash->add('error', 'Email error. Please try again. Message: ' . "\n" . $e->getMessage());
                    $response = $response->withStatus(500);
                    return $this->responder->render($response, 'auth/register.html.php');
                }

                if ($insertId !== false) {
                    $this->logger->info('User "' . $userData['email'] . '" created');
                } else {
                    $this->logger->info('Account creation tried with existing email: "' . $user->getEmail() . '"');
                }
                // Redirect for new user and if email already exists is the same
                return $this->responder->redirectToRouteName($response, 'register-check-email-page');
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