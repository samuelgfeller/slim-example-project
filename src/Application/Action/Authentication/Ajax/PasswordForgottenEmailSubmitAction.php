<?php

namespace App\Application\Action\Authentication\Ajax;

use App\Application\Renderer\RedirectHandler;
use App\Application\Renderer\TemplateRenderer;
use App\Domain\Authentication\Service\PasswordRecoveryEmailSender;
use App\Domain\Exception\DomainRecordNotFoundException;
use App\Domain\Security\Exception\SecurityException;
use App\Domain\Validation\ValidationException;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

final readonly class PasswordForgottenEmailSubmitAction
{
    public function __construct(
        private TemplateRenderer $templateRenderer,
        private RedirectHandler $redirectHandler,
        private SessionInterface $session,
        private PasswordRecoveryEmailSender $passwordRecoveryEmailSender,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @param ServerRequest $request
     * @param Response $response
     *
     * @throws \Throwable
     *
     * @return Response
     */
    public function __invoke(ServerRequest $request, Response $response): Response
    {
        $flash = $this->session->getFlash();
        $userValues = (array)$request->getParsedBody();

        try {
            $this->passwordRecoveryEmailSender->sendPasswordRecoveryEmail($userValues);
        } catch (DomainRecordNotFoundException $domainRecordNotFoundException) {
            // User was not found. Do nothing special as it would be a security flaw to say that user doesn't exist
            $this->logger->info(
                'Someone with email ' . $userValues['email'] . ' tried to request password
                reset link but account doesn\'t exist'
            );
        } catch (ValidationException $validationException) {
            // Form error messages set in function below
            return $this->templateRenderer->renderOnValidationError(
                $response,
                'authentication/login.html.php',
                $validationException,
                $request->getQueryParams(),
            );
        } catch (SecurityException $securityException) {
            return $this->templateRenderer->respondWithFormThrottle(
                $response,
                'authentication/login.html.php',
                $securityException,
                $request->getQueryParams(),
                ['email' => $userValues['email']]
            );
        } catch (TransportExceptionInterface $transportException) {
            $flash->add('error', __('There was an error when sending the email.'));

            return $this->templateRenderer->render(
                $response,
                'authentication/login.html.php',
                $request->getQueryParams(),
            );
        }
        $flash->add(
            'success',
            __(
                "Password recovery email is being sent to you.<br>
Please check the spam folder if you don't see it in the inbox."
            )
        );

        return $this->redirectHandler->redirectToRouteName($response, 'login-page');
    }
}
