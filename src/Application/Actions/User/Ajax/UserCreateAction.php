<?php

namespace App\Application\Actions\User\Ajax;

use App\Application\Responder\Responder;
use App\Domain\Factory\Infrastructure\LoggerFactory;
use App\Domain\User\Service\UserCreator;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

final class UserCreateAction
{
    protected LoggerInterface $logger;

    public function __construct(
        LoggerFactory $logger,
        protected Responder $responder,
        protected UserCreator $userCreator,
        private readonly SessionInterface $session,
    ) {
        $this->logger = $logger->addFileHandler('error.log')->createLogger('user-create-action');
    }

    /**
     * @param ServerRequest $request
     * @param Response $response
     *
     * @throws \Throwable
     * @throws \JsonException
     */
    public function __invoke(ServerRequest $request, Response $response): Response
    {
        $flash = $this->session->getFlash();
        $userValues = $request->getParsedBody();

        // Populate $captcha var if reCAPTCHA response is given
        $captcha = $userValues['g-recaptcha-response'] ?? null;

        try {
            // Throws exception if there is error and returns false if user already exists
            $insertId = $this->userCreator->createUser($userValues, $captcha, $request->getQueryParams());

            if ($insertId !== false) {
                $this->logger->info('User "' . $userValues['email'] . '" created');
            } else {
                $this->logger->info('Account creation tried with existing email: "' . $userValues['email'] . '"');
                $response = $response->withAddedHeader('Warning', 'The post could not be created');
            }

            return $this->responder->respondWithJson($response, ['status' => 'success', 'data' => null], 201);
        } catch (TransportExceptionInterface $e) {
            // Flash message has to be added in the frontend as form is submitted via Ajax
            $this->logger->error('Mailer exception: ' . $e->getMessage());

            return $this->responder->respondWithJson(
                $response,
                ['status' => 'error', 'message' => __('Email error. Please contact an administrator.')]
            );
        }
    }
}
