<?php

namespace App\Module\Authentication\PasswordReset\Action;

use App\Core\Application\Responder\RedirectHandler;
use App\Core\Application\Responder\TemplateRenderer;
use App\Module\Authentication\PasswordReset\Service\PasswordRecoveryEmailSender;
use App\Module\Exception\Domain\DomainRecordNotFoundException;
use App\Module\Exception\Domain\ValidationException;
use App\Module\Security\Domain\Exception\SecurityException;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
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
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     *
     * @throws \Throwable
     *
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
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
