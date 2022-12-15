<?php

namespace App\Test\Unit\Authentication;

use App\Domain\Authentication\Data\UserVerificationData;
use App\Domain\Authentication\Exception\UserAlreadyVerifiedException;
use App\Domain\Authentication\Service\RegisterTokenVerifier;
use App\Domain\User\Data\UserData;
use App\Domain\User\Enum\UserStatus;
use App\Domain\User\Service\UserFinder;
use App\Infrastructure\Authentication\VerificationToken\VerificationTokenFinderRepository;
use App\Test\Traits\AppTestTrait;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Email verification test (after user clicked on link).
 *
 * Important note: with SLE-152 I removed useless unit tests that break when doing changes on the
 * function under test. On this test class I kept only a select case where I see it being useful for now.
 */
class RegisterTokenVerifierTest extends TestCase
{
    use AppTestTrait;

    /**
     * Case when user clicks on the link even though the user is not 'unverified' anymore.
     *
     * Assert that UserAlreadyVerifiedException is thrown when already verified
     *
     * This unit test makes sense because even if UserAlreadyVerifiedException is thrown the server
     * should not return any error (which is tested in RegisterVerifyActionTest.php) so this
     * behaviour can only be tested partly by integration tests.
     * Note: I may remove it in the future if it's annoying to maintain on refactor.
     * As long as the end result both in what the user sees (redirect) and database entries is correct,
     * the way to get there is not really relevant.
     *
     * @dataProvider \App\Test\Provider\Authentication\UserVerificationProvider::userVerificationProvider
     *
     * @param UserVerificationData $verification
     * @param string $clearTextToken
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|\Exception
     */
    public function testGetUserIdIfRegisterTokenIsValidAlreadyVerified(
        UserVerificationData $verification,
        string $clearTextToken
    ): void {
        // Return valid verification object from repository
        $this->mock(VerificationTokenFinderRepository::class)->expects(self::once())->method(
            'findUserVerification'
        )->willReturn($verification);
        // Return active user (empty user, only status is populated)
        $this->mock(UserFinder::class)->expects(self::once())->method('findUserById')->willReturn(
            // IMPORTANT: user has to be already active for exception to be thrown
            new UserData(['status' => UserStatus::Active->value])
        );

        $this->expectException(UserAlreadyVerifiedException::class);
        $this->expectExceptionMessage('User has not status "' . UserStatus::Unverified->value . '"');

        // Call function under test
        $this->container->get(RegisterTokenVerifier::class)->getUserIdIfRegisterTokenIsValid(
            $verification->id,
            $clearTextToken
        );
    }
}
