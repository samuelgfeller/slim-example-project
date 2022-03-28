<?php

namespace App\Domain\Authentication\Service;

use App\Domain\Exceptions\ForbiddenException;
use App\Domain\Exceptions\UnauthorizedException;
use App\Domain\Exceptions\ValidationException;
use App\Domain\Factory\LoggerFactory;
use App\Domain\User\Service\UserValidator;
use App\Infrastructure\Authentication\UserRoleFinderRepository;
use App\Infrastructure\User\UserUpdaterRepository;
use Odan\Session\SessionInterface;
use Psr\Log\LoggerInterface;

class PasswordChanger
{
    private LoggerInterface $logger;
    
public function __construct(
    private UserRoleFinderRepository $userRoleFinderRepository,
    private SessionInterface $session,
    private UserUpdaterRepository $userUpdaterRepository,
    private UserValidator $userValidator,
    LoggerFactory $loggerFactory
) {
    $this->logger = $loggerFactory->addFileHandler('error.log')->createInstance('password-changer');
}

    /**
     * Change password for logged-in user (either from profile or forgotten via token)
     * @param string $password1
     * @param string $password2
     * @param int|null $userId
     *
     * @return bool
     * @throws ValidationException|ForbiddenException
     */
    public function changeUserPassword(string $password1, string $password2, int $userId = null): bool
    {
        if(($loggedInUserId = $this->session->get('user_id')) !== null) {

            // Validate passwords
            $this->userValidator->validatePasswords([$password1, $password2], true);

            // If no user id is provided, change logged-in user password
            $userId = $userId ?? $loggedInUserId;

            $userRole = $this->userRoleFinderRepository->getUserRoleById($loggedInUserId);
            if ($userRole === 'admin' || $userId === $loggedInUserId) {
                $passwordHash = password_hash($password1, PASSWORD_DEFAULT);
                return $this->userUpdaterRepository->changeUserPassword($passwordHash, $userId);
            }

            // User does not have needed rights to access area or function
            $this->logger->warning(
                'User ' . $loggedInUserId . ' tried to change password of other user with id: ' . $userId
            );
            throw new ForbiddenException('Not allowed to change password.');
        }
        throw new UnauthorizedException('Please login to change password.');
    }
}