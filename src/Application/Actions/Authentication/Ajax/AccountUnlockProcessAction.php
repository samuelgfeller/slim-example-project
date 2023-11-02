<?php

namespace App\Application\Actions\Authentication\Ajax;

use App\Application\Responder\Responder;
use App\Domain\Authentication\Exception\InvalidTokenException;
use App\Domain\Authentication\Exception\UserAlreadyVerifiedException;
use App\Domain\Authentication\Service\AccountUnlockTokenVerifier;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;

final class AccountUnlockProcessAction
{

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly Responder $responder,
        private readonly SessionInterface $session,
        private readonly AccountUnlockTokenVerifier $accountUnlockTokenVerifier
    ) {
    }

    public function __invoke(ServerRequest $request, Response $response): Response
    {
        $queryParams = $request->getQueryParams();
        $flash = $this->session->getFlash();
        // There may be other query params e.g. redirect
        if (isset($queryParams['id'], $queryParams['token'])) {
            try {
                $userId = $this->accountUnlockTokenVerifier->getUserIdIfUnlockTokenIsValid(
                    (int)$queryParams['id'],
                    $queryParams['token']
                );

                // Log user in
                // Clear all session data and regenerate session ID
                $this->session->regenerateId();
                // Add user to session
                $this->session->set('user_id', $userId);

                if (isset($queryParams['redirect'])) {
                    $flash->add(
                        'success',
                        sprintf(
                            __('Congratulations!<br>Your account has been %s! <br><b>%s</b>'),
                            __('unlocked'),
                            __('You are now logged in.'),
                        )
                    );

                    return $this->responder->redirectToUrl($response, $queryParams['redirect']);
                }

                return $this->responder->redirectToRouteName($response, 'home-page');
            } catch (InvalidTokenException $ite) {
                $flash->add(
                    'error',
                    __('Invalid or expired link. Please <b>log in</b> to receive a new link.')
                );
                $this->logger->error('Invalid or expired token user_verification id: ' . $queryParams['id']);
                $newQueryParam = isset($queryParams['redirect']) ? ['redirect' => $queryParams['redirect']] : [];

                // Redirect to login page with redirect query param if set
                return $this->responder->redirectToRouteName($response, 'login-page', [], $newQueryParam);
            } catch (UserAlreadyVerifiedException $uave) {
                $flash->add('info', $uave->getMessage());
                $this->logger->info(
                    'Not locked user tried to unlock account. user_verification id: ' . $queryParams['id']
                );
                $newQueryParam = isset($queryParams['redirect']) ? ['redirect' => $queryParams['redirect']] : [];

                return $this->responder->redirectToRouteName(
                    $response,
                    'login-page',
                    [],
                    $newQueryParam
                );
            }
        }

        $flash->add('error', __('Token not found. Please click on the link you received via email.'));
        // Prevent to log passwords
        $this->logger->error('GET request malformed: ' . json_encode($queryParams, JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR));
        // Caught in error handler which displays error page because if POST request body is empty frontend has error
        // Error message same as in tests/Provider/UserProvider->malformedRequestBodyProvider()
        throw new HttpBadRequestException($request, 'Query params malformed.');
    }
}
