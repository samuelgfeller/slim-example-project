<?php

namespace App\Module\User\Create\Action;

use App\Application\Responder\JsonResponder;
use App\Module\User\Create\Service\UserCreator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

final readonly class UserCreateAction
{
    public function __construct(
        private LoggerInterface $logger,
        private JsonResponder $jsonResponder,
        private UserCreator $userCreator,
    ) {
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     *
     * @throws \Throwable
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $userValues = (array)$request->getParsedBody();

        try {
            // Throws exception if there is an error and returns false if user already exists
            $insertId = $this->userCreator->createUser($userValues);

            if ($insertId !== false) {
                $this->logger->info('User "' . $userValues['email'] . '" created');

                return $this->jsonResponder->encodeAndAddToResponse(
                    $response,
                    ['status' => 'success', 'data' => null],
                    201
                );
            }

            return $this->jsonResponder->encodeAndAddToResponse($response, [
                'status' => 'warning',
                'message' => 'User not created',
            ]);
        } catch (TransportExceptionInterface $e) {
            // Flash message has to be added in the frontend as form is submitted via Ajax
            $this->logger->error('Mailer exception: ' . $e->getMessage());

            return $this->jsonResponder->encodeAndAddToResponse(
                $response,
                ['status' => 'error', 'message' => __('Email error. Please contact an administrator.')]
            );
        }
    }
}
