<?php

namespace App\Application\Actions\Authentication;

use App\Application\Responder\Responder;
use App\Domain\Authentication\Service\PasswordChanger;
use App\Domain\Authentication\Service\PasswordVerifier;
use App\Domain\Exceptions\InvalidCredentialsException;
use App\Domain\Exceptions\ValidationException;
use App\Domain\Factory\LoggerFactory;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;

/**
 * When user wants to change password being authenticated
 */
class ChangePasswordSubmitAction
{
    private LoggerInterface $logger;

    /**
     * The constructor.
     *
     * @param Responder $responder
     */
    public function __construct(
        private Responder $responder,
        private SessionInterface $session,
        private PasswordVerifier $passwordVerifier,
        private PasswordChanger $passwordChanger,
        LoggerFactory $loggerFactory
    ) {
        $this->logger = $loggerFactory->addFileHandler('error.log')->createInstance('user-service');
    }

    /**
     * Check if token is valid and if yes display password form
     *
     * @param ServerRequest $request
     * @param Response $response
     * @return Response
     * @throws \Throwable
     */
    public function __invoke(ServerRequest $request, Response $response): Response
    {
        $parsedBody = $request->getParsedBody();
        $flash = $this->session->getFlash();

        if (isset($parsedBody['old_password'], $parsedBody['password'], $parsedBody['password2'])) {
            try {
                // Throws exception if not valid
                $this->passwordVerifier->verifyPassword($parsedBody['old_password']);

                // Calls service function to change password
                $this->passwordChanger->changeUserPassword($parsedBody['password'], $parsedBody['password2']);

                $flash->add('success', 'Successfully changed password.');

                return $this->responder->redirectToRouteName($response, 'profile-page');
            } catch (InvalidCredentialsException $invalidCredentialsException) {
                $flash->add('error', '<b>Wrong old password. <br> Please try again.');

                // Redirect to password change page
                return $this->responder->render(
                    $response,
                    'authentication/change-password.html.php',
                    // Provide directly bool value if old password was wrong instead of validation error format for simplicity
                    ['formError' => true, 'oldPasswordErr'=> true]
                );
            } catch (ValidationException $validationException) {
                $flash->add('error', $validationException->getMessage());
                return $this->responder->renderOnValidationError(
                    $response,
                    'authentication/change-password.html.php',
                    $validationException->getValidationResult(),
                    $request->getQueryParams()
                );
            }
        }

        $flash->add('error', 'There is something wrong with the request body.');
        // Prevent to log passwords
        $this->logger->error(
            'Password change request malformed. Array keys: ' . json_encode(array_keys($parsedBody), JSON_THROW_ON_ERROR)
        );
        // Caught in error handler which displays error page because if POST request body is empty frontend has error
        throw new HttpBadRequestException($request, 'Password change request malformed.');
    }
}