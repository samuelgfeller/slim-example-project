<?php

namespace App\Application\Actions\Authentication\Submit;

use App\Application\Responder\Responder;
use App\Application\Validation\MalformedRequestBodyChecker;
use App\Domain\Authentication\Exception\InvalidTokenException;
use App\Domain\Authentication\Service\PasswordChanger;
use App\Domain\Factory\LoggerFactory;
use App\Domain\Validation\ValidationException;
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
     * @param SessionInterface $session
     * @param PasswordChanger $passwordChanger
     * @param MalformedRequestBodyChecker $malformedRequestBodyChecker
     * @param LoggerFactory $loggerFactory
     */
    public function __construct(
        private readonly Responder $responder,
        private readonly SessionInterface $session,
        private readonly PasswordChanger $passwordChanger,
        private readonly MalformedRequestBodyChecker $malformedRequestBodyChecker,
        LoggerFactory $loggerFactory
    ) {
        $this->logger = $loggerFactory->addFileHandler('error.log')->createInstance('user-service');
    }

    /**
     * Check if token is valid and if yes display password form.
     *
     * @param ServerRequest $request
     * @param Response $response
     *
     * @throws \Throwable
     *
     * @return Response
     */
    public function __invoke(ServerRequest $request, Response $response): Response
    {
        $parsedBody = $request->getParsedBody();
        $flash = $this->session->getFlash();

        // There may be other query params e.g. redirect
        if ($this->malformedRequestBodyChecker->requestBodyHasValidKeys(
            $parsedBody,
            ['id', 'token', 'password', 'password2'],
            ['redirect']
        )) {
            try {
                $this->passwordChanger->resetPasswordWithToken(
                    $parsedBody['password'],
                    $parsedBody['password2'],
                    (int)$parsedBody['id'],
                    $parsedBody['token']
                );

                $flash->add('success', 'Successfully changed password. <b>You are now logged in.</b>');

                return $this->responder->redirectToRouteName($response, 'profile-page');
            } catch (InvalidTokenException $ite) {
                $this->responder->addPhpViewAttribute(
                    'formErrorMessage',
                    '<b>Invalid, used or expired link. <br> Please request a new link below and make sure to click on ' .
                    'the most recent email we sent to you</a>.</b>'
                );
                // Pre-fill email input field for more user comfort. The usefulness of this specific situation may be
                // questionable but this is here to serve as inspiration. My goal is a program user LOVE to use.
                if ($ite->userData->email !== null) {
                    $this->responder->addPhpViewAttribute('preloadValues', ['email' => $ite->userData->email]);
                }

                $this->logger->error(
                    'Invalid or expired token password reset user_verification id: ' . $parsedBody['id']
                );
                // Render password
                return $this->responder->render($response, 'authentication/login.html.php');
            } catch (ValidationException $validationException) {
                $flash->add('error', $validationException->getMessage());
                // Add token and id to php view attribute like PasswordResetAction does
                $this->responder->addPhpViewAttribute('token', $parsedBody['token']);
                $this->responder->addPhpViewAttribute('id', $parsedBody['id']);

                return $this->responder->renderOnValidationError(
                    $response,
                    'authentication/reset-password.html.php',
                    $validationException,
                    $request->getQueryParams(),
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
