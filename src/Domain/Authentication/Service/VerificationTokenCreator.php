<?php


namespace App\Domain\Authentication\Service;


use App\Application\Actions\Authentication\AuthenticationMailer;
use App\Domain\User\DTO\User;
use App\Domain\Utility\Mailer;
use App\Infrastructure\Authentication\VerificationToken\VerificationTokenCreatorRepository;
use App\Infrastructure\Authentication\VerificationToken\VerificationTokenDeleterRepository;

class VerificationTokenCreator
{

    public function __construct(
        private VerificationTokenDeleterRepository $verificationTokenDeleterRepository,
        private AuthenticationMailer $mailer,
        private VerificationTokenCreatorRepository $verificationTokenCreatorRepository
    )
    {
    }

    /**
     * Create and insert verification token
     *
     * @param User $user WITH id
     * @param array $queryParams query params that should be added to email verification link (e.g. redirect)
     *
     * @return int
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function createAndSendUserVerification(User $user, array $queryParams = []): int
    {
        // Create token
        $token = random_bytes(50);

        // Set token expiration because link automatically logs in
        $expires = new \DateTime('now');
        $expires->add(new \DateInterval('PT02H')); // 2 hours

        // Soft delete any existing tokens for this user
        $this->verificationTokenDeleterRepository->deleteVerificationToken($user->id);

        // Insert verification token into database
        $tokenId = $this->verificationTokenCreatorRepository->insertUserVerification(
            [
                'user_id' => $user->id,
                'token' => password_hash($token, PASSWORD_DEFAULT),
                // expires format 'U' is the same as time() so it can be used later to compare easily
                'expires' => $expires->format('U')
            ]
        );

        // Add relevant query params to $queryParams array
        $queryParams['token'] = $token;
        $queryParams['id'] = $tokenId;

        // PHPMailer errors caught in action
        $this->mailer->sendRegisterVerificationToken($user, $queryParams);

        return $tokenId;
    }
}