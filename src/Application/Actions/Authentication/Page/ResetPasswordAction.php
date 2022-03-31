<?php

namespace App\Application\Actions\Authentication\Page;

use App\Application\Responder\Responder;
use App\Domain\Authentication\Service\VerificationTokenVerifier;
use App\Domain\Factory\LoggerFactory;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;

class ResetPasswordAction
{
    private LoggerInterface $logger;

    /**
     * The constructor.
     *
     * @param Responder $responder
     */
    public function __construct(
        private Responder $responder,
        private SessionInterface $session,
        private VerificationTokenVerifier $verificationTokenChecker,
        LoggerFactory $loggerFactory
    ) {
        $this->logger = $loggerFactory->addFileHandler('error.log')->createInstance('user-service');
    }

    /**
     * Check if token is valid and if yes display password form
     *
     * @param ServerRequest $request
     * @param Response $response
     * @return Response
     * @throws \Throwable
     */
    public function __invoke(ServerRequest $request, Response $response): Response
    {
        $queryParams = $request->getQueryParams();
        $flash = $this->session->getFlash();

        // There may be other query params e.g. redirect
        if (isset($queryParams['id'], $queryParams['token'])) {
            return $this->responder->render($response, 'authentication/set-new-password.html.php', [
                    'token' => $queryParams['token'],
                    'id' => $queryParams['id']
                ]);
        }

        $flash->add('error', 'Please click on the link you received via email.');
        // Prevent to log passwords
        $this->logger->error('GET request malformed: ' . json_encode($queryParams));
        // Caught in error handler which displays error page because if POST request body is empty frontend has error
        // Error message same as in tests/Provider/UserProvider->malformedRequestBodyProvider()
        throw new HttpBadRequestException($request, 'Query params malformed.');
    }
}