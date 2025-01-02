<?php

namespace App\Modules\Authentication\Action\Ajax;

use App\Core\Application\Responder\RedirectHandler;
use App\Modules\Authentication\Domain\Exception\InvalidTokenException;
use App\Modules\Authentication\Domain\Exception\UserAlreadyVerifiedException;
use App\Modules\Authentication\Domain\Service\AccountUnlockTokenVerifier;
use Odan\Session\SessionInterface;
use Odan\Session\SessionManagerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;

final readonly class AccountUnlockProcessAction
{
    public function __construct(
        private LoggerInterface $logger,
        private RedirectHandler $redirectHandler,
        private SessionManagerInterface $sessionManager,
        private SessionInterface $session,
        private AccountUnlockTokenVerifier $accountUnlockTokenVerifier,
    ) {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $queryParams = $request->getQueryParams();
        $flash = $this->session->getFlash();

        // There may be other query params, e.g. redirect
        // Check for required query params id and token
        if (!isset($queryParams['id'], $queryParams['token'])) {
            $flash->add('error', __('Token not found. Please click on the link you received via email.'));
            // Prevent logging sensitive token
            $queryParams['token'] = '***';
            $this->logger->error(
                'GET request malformed: ' . json_encode(
                    $queryParams,
                    JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR
                )
            );
            // Caught in error handler which displays error page
            throw new HttpBadRequestException($request, 'Query params malformed.');
        }

        try {
            $userId = $this->accountUnlockTokenVerifier->verifyUnlockTokenAndGetUserId(
                (int)$queryParams['id'],
                $queryParams['token']
            );

            // Log user in
            // Clear all session data and regenerate session ID
            $this->sessionManager->regenerateId();
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

                return $this->redirectHandler->redirectToUrl($response, $queryParams['redirect']);
            }

            return $this->redirectHandler->redirectToRouteName($response, 'home-page');
        } catch (InvalidTokenException $invalidTokenException) {
            $flash->add(
                'error',
                __('Invalid or expired link. Please <b>log in</b> to receive a new link.')
            );
            $this->logger->error('Invalid or expired token user_verification id: ' . $queryParams['id']);
            $newQueryParam = isset($queryParams['redirect']) ? ['redirect' => $queryParams['redirect']] : [];

            // Redirect to login page with redirect query param if set
            return $this->redirectHandler->redirectToRouteName($response, 'login-page', [], $newQueryParam);
        } catch (UserAlreadyVerifiedException $alreadyVerifiedException) {
            $flash->add('info', $alreadyVerifiedException->getMessage());
            $this->logger->info(
                'Not locked user tried to unlock account. user_verification id: ' . $queryParams['id']
            );
            $newQueryParam = isset($queryParams['redirect']) ? ['redirect' => $queryParams['redirect']] : [];

            return $this->redirectHandler->redirectToRouteName(
                $response,
                'login-page',
                [],
                $newQueryParam
            );
        }
    }
}
