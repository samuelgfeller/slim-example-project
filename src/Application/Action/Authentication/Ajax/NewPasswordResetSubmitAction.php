<?php

namespace App\Application\Action\Authentication\Ajax;

use App\Application\Renderer\RedirectHandler;
use App\Application\Renderer\TemplateRenderer;
use App\Domain\Authentication\Exception\InvalidTokenException;
use App\Domain\Authentication\Service\PasswordResetterWithToken;
use App\Domain\Validation\ValidationException;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;
use Psr\Log\LoggerInterface;

readonly class NewPasswordResetSubmitAction
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
     * Check if token is valid and if yes display password form.
     *
     * @param ServerRequest $request
     * @param Response $response
     *
     * @return Response
     * @throws \Throwable
     *
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

            return $this->redirectHandler->redirectToRouteName($response, 'login-page');
        } catch (InvalidTokenException $ite) {
            $this->templateRenderer->addPhpViewAttribute(
                'formErrorMessage',
                __( // Message below asserted in PasswordResetSubmitActionTest
                    '<b>Invalid, used or expired link. <br> Please request a new link below and make 
sure to click on the most recent email we send you</a>.</b>'
                )
            );
            // Pre-fill email input field for more user comfort.
            if ($ite->userData->email !== null) {
                $this->templateRenderer->addPhpViewAttribute('preloadValues', ['email' => $ite->userData->email]);
            }

            $this->logger->error(
                'Invalid or expired token password reset user_verification id: ' . $parsedBody['id']
            );

            // The login page is rendered, but the url is reset-password. In login-main.js the url is replaced, and
            // the password-forgotten form is shown instead of the login form.
            return $this->templateRenderer->render($response, 'authentication/login.html.php');
        } // Validation Exception has to be caught here and not middleware as the token,
            // and id have to be added to php view
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
