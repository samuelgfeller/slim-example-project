<?php

namespace App\Application\Actions\Authentication;

use App\Application\Responder\Responder;
use App\Domain\Authentication\Exception\InvalidTokenException;
use App\Domain\Authentication\Service\PasswordChanger;
use App\Domain\Authentication\Service\VerificationTokenVerifier;
use App\Domain\Exceptions\ValidationException;
use App\Domain\Factory\LoggerFactory;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;

class PasswordResetSubmitAction
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
        private VerificationTokenVerifier $verificationTokenChecker,
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

        // There may be other query params e.g. redirect
        if (isset($parsedBody['id'], $parsedBody['token'], $parsedBody['password'], $parsedBody['password2'])) {
            try {
                $userId = $this->verificationTokenChecker->getUserIdIfTokenIsValid(
                    (int)$parsedBody['id'],
                    $parsedBody['token'],
                );

                // Log user in
                // Clear all session data and regenerate session ID
                $this->session->regenerateId();
                // Add user to session
                $this->session->set('user_id', $userId);

                // Call function to change password AFTER login as it's used for normal password change too
                $this->passwordChanger->changeUserPassword($parsedBody['password'], $parsedBody['password2']);

                $flash->add('success', 'Successfully changed password. <b>You are now logged in.</b>');

                return $this->responder->redirectToRouteName($response, 'profile-page');
            } catch (InvalidTokenException $ite) {
                $flash->add(
                    'error',
                    '<b>Invalid, used or expired link. <br> Please <a href="' . $this->responder->urlFor(
                        'password-forgotten-page'
                    ) . '">ask for a new link</a>.</b>'
                );
                $this->logger->error(
                    'Invalid or expired token password reset user_verification id: ' . $parsedBody['id']
                );
                // Redirect to login page
                return $this->responder->redirectToRouteName($response, 'login-page');
            } catch (ValidationException $validationException) {
                $flash->add('error', $validationException->getMessage());
                // Add token and id to php view attribute like PasswordResetAction does
                $this->responder->addPhpViewAttribute('token', $parsedBody['token']);
                $this->responder->addPhpViewAttribute('id', $parsedBody['id']);
                return $this->responder->renderOnValidationError(
                    $response,
                    'authentication/reset-password.html.php',
                    $validationException->getValidationResult()
                );
            }
        }

        $flash->add('error', 'Please click on the link you received via email.');
        // Prevent to log passwords
        $this->logger->error(
            'Password change request malformed: ' . json_encode($parsedBody, JSON_THROW_ON_ERROR)
        );
        // Caught in error handler which displays error page because if POST request body is empty frontend has error
        // Exception message same as in tests/Provider/UserProvider->malformedPasswordResetRequestBodyProvider()
        throw new HttpBadRequestException($request, 'Request body malformed.');
    }
}