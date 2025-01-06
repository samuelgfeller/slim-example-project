<?php

namespace App\Module\Authentication\PasswordReset\Action;

use App\Core\Application\Responder\RedirectHandler;
use App\Core\Application\Responder\TemplateRenderer;
use App\Module\Authentication\PasswordReset\Service\PasswordResetterWithToken;
use App\Module\Authentication\TokenVerification\Exception\InvalidTokenException;
use App\Module\Exception\Domain\ValidationException;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final readonly class NewPasswordResetSubmitAction
{
    public function __construct(
        private TemplateRenderer $templateRenderer,
        private RedirectHandler $redirectHandler,
        private SessionInterface $session,
        private PasswordResetterWithToken $passwordResetterWithToken,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * Check if the token is valid and if yes display password form.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     *
     * @throws \Throwable
     *
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $parsedBody = (array)$request->getParsedBody();
        $flash = $this->session->getFlash();

        try {
            $this->passwordResetterWithToken->resetPasswordWithToken($parsedBody);

            $flash->add(
                'success',
                __('Successfully changed password. <b>%s</b>', __('Please log in.'))
            );

            return $this->redirectHandler->redirectToRouteName($response, 'login-page');
        } catch (InvalidTokenException $invalidTokenException) {
            $this->templateRenderer->addPhpViewAttribute(
                'formErrorMessage',
                __( // Message below asserted in PasswordResetSubmitActionTest
                    '<b>Invalid, used or expired link. <br> Please request a new link below and make 
sure to click on the most recent email we send you</a>.</b>'
                )
            );
            // Pre-fill email input field for more user comfort.
            if ($invalidTokenException->userData->email !== null) {
                $this->templateRenderer->addPhpViewAttribute(
                    'preloadValues',
                    ['email' => $invalidTokenException->userData->email]
                );
            }

            $this->logger->error(
                'Invalid or expired token password reset user_verification id: ' . $parsedBody['id']
            );

            // The login page is rendered, but the url is reset-password. In login-main.js the url is replaced, and
            // the password-forgotten form is shown instead of the login form.
            return $this->templateRenderer->render($response, 'authentication/login.html.php');
        } // Validation Exception has to be caught here and not middleware as the token, and id are added to php view
        catch (ValidationException $validationException) {
            // Render reset-password form with token, and id so that it can be submitted again
            $flash->add('error', $validationException->getMessage());
            // Add token and id to php view attribute like PasswordResetAction does
            $this->templateRenderer->addPhpViewAttribute('token', $parsedBody['token']);
            $this->templateRenderer->addPhpViewAttribute('id', $parsedBody['id']);

            return $this->templateRenderer->renderOnValidationError(
                $response,
                'authentication/reset-password.html.php',
                $validationException,
                $request->getQueryParams(),
            );
        }
    }
}
