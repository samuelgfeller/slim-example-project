<?php

namespace App\Application\Actions\User;

use App\Application\Responder\Responder;
use App\Application\Validation\MalformedRequestBodyChecker;
use App\Domain\Exceptions\ValidationException;
use App\Domain\Factory\LoggerFactory;
use App\Domain\Security\Exception\SecurityException;
use App\Domain\User\Service\UserCreator;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

final class UserCreateSubmitAction
{
    protected LoggerInterface $logger;

    public function __construct(
        LoggerFactory $logger,
        protected Responder $responder,
        protected UserCreator $userRegisterer,
        private readonly SessionInterface $session,
        private readonly MalformedRequestBodyChecker $malformedRequestBodyChecker,
    ) {
        $this->logger = $logger->addFileHandler('error.log')->createInstance('user-create-action');
    }

    public function __invoke(ServerRequest $request, Response $response): Response
    {
        $flash = $this->session->getFlash();
        $userValues = $request->getParsedBody();

        if (null !== $userValues && [] !== $userValues) {
            if ($this->malformedRequestBodyChecker->requestBodyHasValidKeys($userValues, [
                'first_name',
                'surname',
                'email',
                'status',
                'user_role_id',
                'password',
                'password2',
            ], ['g-recaptcha-response'])) {
                // Populate $captcha var if reCAPTCHA response is given
                $captcha = $userValues['g-recaptcha-response'] ?? null;

                try {
                    // Throws exception if there is error and returns false if user already exists
                    $insertId = $this->userRegisterer->createUser($userValues, $captcha, $request->getQueryParams());
                    // Say email has been sent even when user exists as it should be kept secret
                    $flash->add('success', 'Email has been sent.');
                    $flash->add('warning',
                        'Your account is not active yet. <br>
Please click on the link in the email to finnish the registration.'
                    );
                } catch (ValidationException $validationException) {
                    return $this->responder->respondWithJsonOnValidationError(
                        $validationException->getValidationResult(),
                        $response
                    );
                } catch (TransportExceptionInterface $e) {
                    $flash->add('error', 'Email error. Please try again. ' . "<br> Message: " . $e->getMessage());
                    $this->logger->error('Mailer exception: ' . $e->getMessage());
                    $response = $response->withStatus(500);
                    $this->responder->addPhpViewAttribute('formError', true);
                    return $this->responder->render(
                        $response,
                        'authentication/register.html.php',
                        // Provide same query params passed to register page to be added again to the submit request
                        ['queryParams' => $request->getQueryParams()]
                    );
                } catch (SecurityException $se) {
                    if (PHP_SAPI === 'cli') {
                        // If script is called from commandline (e.g. testing) throw error instead of rendering page
                        throw $se;
                    }

                    return $this->responder->respondWithFormThrottle(
                        $response,
                        'authentication/register.html.php',
                        $se,
                        $request->getQueryParams(),
                        [
                            'firstName' => $userValues['first_name'],
                            'surname' => $userValues['surname'],
                            'email' => $userValues['email']
                        ],
                    );
                }

                if ($insertId !== false) {
                    $this->logger->info('User "' . $userValues['email'] . '" created');
                } else {
                    $this->logger->info('Account creation tried with existing email: "' . $userValues['email'] . '"');
                }
                // Redirect for new user and if email already exists is the same
//                return $response;
                return $this->responder->redirectToRouteName($response, 'register-check-email-page');
            }
            $flash->add('error', 'Malformed request body syntax');
            // Prevent to log passwords; if keys not set unset() will not trigger notice or warning
            unset($userValues['password'], $userValues['password2']);
            $this->logger->error('POST request body malformed: ' . json_encode($userValues));
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