<?php

namespace App\Application\Actions\Authentication\Ajax;

use App\Application\Responder\Responder;
use App\Domain\Authentication\Exception\InvalidTokenException;
use App\Domain\Authentication\Service\PasswordResetterWithToken;
use App\Domain\Factory\Infrastructure\LoggerFactory;
use App\Domain\Validation\ValidationException;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;
use Psr\Log\LoggerInterface;

class NewPasswordResetSubmitAction
{
    private LoggerInterface $logger;

    public function __construct(
        private readonly Responder $responder,
        private readonly SessionInterface $session,
        private readonly PasswordResetterWithToken $passwordResetterWithToken,
        LoggerFactory $loggerFactory
    ) {
        $this->logger = $loggerFactory->addFileHandler('error.log')->createLogger('user-service');
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
        $parsedBody = (array)$request->getParsedBody();
        $flash = $this->session->getFlash();

        try {
            $this->passwordResetterWithToken->resetPasswordWithToken($parsedBody);

            $flash->add(
                'success',
                sprintf(__('Successfully changed password. <b>%s</b>'), __('Please log in.'))
            );

            return $this->responder->redirectToRouteName($response, 'login-page');
        } catch (InvalidTokenException $ite) {
            $this->responder->addPhpViewAttribute(
                'formErrorMessage',
                __(
                    '<b>Invalid, used or expired link. <br> Please request a new link below and make 
sure to click on the most recent email we send you</a>.</b>'
                )
            );
            // Pre-fill email input field for more user comfort.
            if ($ite->userData->email !== null) {
                $this->responder->addPhpViewAttribute('preloadValues', ['email' => $ite->userData->email]);
            }

            $this->logger->error(
                'Invalid or expired token password reset user_verification id: ' . $parsedBody['id']
            );

            // The login page is rendered but the url is reset-password. In login-main.js the url is replaced and
            // the password forgotten form is shown instead of the login form.
            return $this->responder->render($response, 'authentication/login.html.php');
        } // Validation Exception has to be caught here and not middleware as we need to add token and id to php view
        catch (ValidationException $validationException) {
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
}
