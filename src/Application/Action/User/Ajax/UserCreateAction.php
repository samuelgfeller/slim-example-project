<?php

namespace App\Application\Action\User\Ajax;

use App\Application\Renderer\JsonEncoder;
use App\Domain\User\Service\UserCreator;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

final readonly class UserCreateAction
{
    public function __construct(
        private LoggerInterface $logger,
        private JsonEncoder $jsonEncoder,
        private UserCreator $userCreator,
    ) {
    }

    /**
     * @param ServerRequest $request
     * @param Response $response
     *
     * @throws \Throwable
     */
    public function __invoke(ServerRequest $request, Response $response): Response
    {
        $userValues = (array)$request->getParsedBody();

        try {
            // Throws exception if there is error and returns false if user already exists
            $insertId = $this->userCreator->createUser($userValues, $request->getQueryParams());

            if ($insertId !== false) {
                $this->logger->info('User "' . $userValues['email'] . '" created');
            } else {
                $this->logger->info('Account creation tried with existing email: "' . $userValues['email'] . '"');
                $response = $response->withAddedHeader('Warning', 'The post could not be created');
            }

            return $this->jsonEncoder->encodeAndAddToResponse($response, ['status' => 'success', 'data' => null], 201);
        } catch (TransportExceptionInterface $e) {
            // Flash message has to be added in the frontend as form is submitted via Ajax
            $this->logger->error('Mailer exception: ' . $e->getMessage());

            return $this->jsonEncoder->encodeAndAddToResponse(
                $response,
                ['status' => 'error', 'message' => __('Email error. Please contact an administrator.')]
            );
        }
    }
}
