<?php

namespace App\Application\Actions\Authentication;

use App\Application\Responder\Responder;
use App\Domain\Authentication\Service\PasswordRecoveryEmailSender;
use App\Domain\Exceptions\DomainRecordNotFoundException;
use App\Domain\Factory\LoggerFactory;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;

final class PasswordForgottenEmailSubmitAction
{
    protected LoggerInterface $logger;

    /**
     * The constructor.
     *
     * @param Responder $responder
     * @param SessionInterface $session
     * @param PasswordRecoveryEmailSender $passwordRecoveryEmailSender
     * @param LoggerFactory $logger
     */
    public function __construct(
        private Responder $responder,
        private SessionInterface $session,
        private PasswordRecoveryEmailSender $passwordRecoveryEmailSender,
        LoggerFactory $logger,

    ) {
        $this->logger = $logger->addFileHandler('error.log')->createInstance('auth-login');
    }

    /**
     * @param ServerRequest $request
     * @param Response $response
     * @return Response
     * @throws \Throwable
     */
    public function __invoke(ServerRequest $request, Response $response): Response
    {
        $flash = $this->session->getFlash();
        $userData = $request->getParsedBody();

        // Check that request body syntax is formatted right
        if (null !== $userData && [] !== $userData && isset($userData['email'])) {
            try {
                $this->passwordRecoveryEmailSender->sendPasswordRecoveryEmail($userData);
            } catch (DomainRecordNotFoundException $domainRecordNotFoundException) {
                // User was not found. Do nothing special as it would be a security flaw to tell the client if user exists
            }
            $flash->add('success', 'Password recovery Email is sent. Please check your inbox and the 
            spam folder if needed.');
            $this->responder->redirectToRouteName($response, 'login-page');
        }

        $flash->add('error', 'Malformed request body syntax');
        $this->logger->error('POST request body malformed: ' . json_encode($userData));
        // Caught in error handler which displays error page because if POST request body is empty frontend has error
        throw new HttpBadRequestException($request, 'Request body malformed.');
    }
}