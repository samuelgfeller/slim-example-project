<?php

namespace App\Application\Actions\Auth;

use App\Application\Responder\Responder;
use App\Domain\Auth\AuthService;
use App\Domain\Factory\LoggerFactory;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpForbiddenException;

final class RegisterVerifyAction
{
    protected LoggerInterface $logger;

    public function __construct(
        LoggerFactory $logger,
        protected Responder $responder,
        protected AuthService $authService,
        private SessionInterface $session
    ) {
        $this->logger = $logger->addFileHandler('error.log')->createInstance('auth-verify-register');
    }

    public function __invoke(ServerRequest $request, Response $response): Response
    {
        $queryParams = $request->getQueryParams();
        $flash = $this->session->getFlash();

        if (isset($queryParams['id'], $queryParams['token'])) {
            if (true === $this->authService->verifyUser((int)$queryParams['id'], $queryParams['token'])) {
                $flash->add('success', 'Congratulations! Account verified! <br><b>You are now logged in.</b>');

                // Log user in
                $userId = $this->authService->getUserIdFromVerification($queryParams['id']);
                // Clear all session data and regenerate session ID
                $this->session->regenerateId();
                // Add user to session
                $this->session->set('user_id', $userId);

                return $this->responder->redirectToRouteName($response, 'home');
            }
            $flash->add('error', 'Invalid token');
            $this->logger->error('Invalid token ' . json_encode($queryParams));
            throw new HttpForbiddenException($request,'Invalid token');
        }

        $flash->add('error', 'Please click on the link you received via email.');
        // Prevent to log passwords
        $this->logger->error('GET request body malformed: ' . json_encode($queryParams));
        // Caught in error handler which displays error page because if POST request body is empty frontend has error
        // Error message same as in tests/Provider/UserProvider->malformedRequestBodyProvider()
        throw new HttpBadRequestException($request, 'Query params malformed.');
    }
}