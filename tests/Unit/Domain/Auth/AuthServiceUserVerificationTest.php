<?php

namespace App\Test\Unit\Domain\Auth;

use App\Domain\Auth\AuthService;
use App\Domain\Auth\DTO\UserVerification;
use App\Domain\Auth\Exception\InvalidTokenException;
use App\Domain\Auth\Exception\UserAlreadyVerifiedException;
use App\Domain\User\User;
use App\Infrastructure\User\UserRepository;
use App\Infrastructure\User\UserVerificationRepository;
use App\Test\AppTestTrait;
use PHPUnit\Framework\TestCase;

/**
 * Email verification test (after user clicked on link)
 */
class AuthServiceUserVerificationTest extends TestCase
{
    use AppTestTrait;

    /**
     * Test that with valid values all security checks pass until changeUserStatus is called
     *
     * @dataProvider \App\Test\Provider\UserVerificationProvider::verifyUserProvider
     * @param UserVerification $verification
     * @param string $clearTextToken
     */
    public function testVerifyUser(UserVerification $verification, string $clearTextToken): void
    {
        // Create mocks
        $userVerificationRepository = $this->mock(UserVerificationRepository::class);
        $userRepository = $this->mock(UserRepository::class);

        // Return valid verification object from repository
        $userVerificationRepository->method('findUserVerification')->willReturn($verification);

        // Return unverified user (empty user, only status is populated)
        $userRepository->expects(self::once())->method('findUserById')->willReturn(
            // IMPORTANT: user has to be unverified for the test to succeed
            new User(['status' => User::STATUS_UNVERIFIED])
        );
        // Making sure that changeUserStatus is called
        $userRepository->expects(self::once())->method('changeUserStatus')->willReturn(true);
        // Assert that setVerificationEntryToUsed is called
        $userVerificationRepository->expects(self::once())->method('setVerificationEntryToUsed')->willReturn(true);

        $authService = $this->container->get(AuthService::class);
        // Call function under test
        self::assertTrue($authService->verifyUser($verification->id, $clearTextToken));
    }

    /**
     * Case when user clicks on the link even though the user is not 'unverified' anymore
     *
     * @dataProvider \App\Test\Provider\UserVerificationProvider::verifyUserProvider
     * @param UserVerification $verification
     * @param string $clearTextToken
     */
    public function testVerifyUser_alreadyVerified(UserVerification $verification, string $clearTextToken): void
    {
        // Return valid verification object from repository
        $this->mock(UserVerificationRepository::class)->expects(self::once())->method(
            'findUserVerification'
        )->willReturn($verification);
        // Return active user (empty user, only status is populated)
        $this->mock(UserRepository::class)->expects(self::once())->method('findUserById')->willReturn(
            // IMPORTANT: user has to be already active for exception to be thrown
            new User(['status' => User::STATUS_ACTIVE])
        );

        $this->expectException(UserAlreadyVerifiedException::class);
        $this->expectExceptionMessage('User has not status "' . User::STATUS_UNVERIFIED . '"');

        // Call function under test
        $this->container->get(AuthService::class)->verifyUser($verification->id, $clearTextToken);
    }

    /**
     * Link in email contains the verification db entry id and if this id is incorrect (token not found)
     * according exception should be thrown
     */
    public function testRegisterUser_notExistingToken(): void
    {
        // Return empty verification object from repository. That means that entry was not found
        $this->mock(UserVerificationRepository::class)->expects(self::once())->method(
            'findUserVerification'
        )->willReturn(new UserVerification()); // Empty class means nothing was found

        // Code should never have to user user repo but if it does, it is mocked to prevent db change
        $this->mock(UserRepository::class);

        $verificationId = 1;
        $this->expectException(InvalidTokenException::class);
        $this->expectExceptionMessage('No token was found for id "' . $verificationId . '".');

        // Call function under test with invalid verification id (token doesn't matter in this test)
        $this->container->get(AuthService::class)->verifyUser($verificationId, 'wrongTokenButItDoesntMatter');
    }

    /**
     * Test when token is invalid or expired
     *
     * Provider gives once an invalid token and once an expired one
     * @dataProvider \App\Test\Provider\UserVerificationProvider::invalidExpiredToken
     *
     * @param UserVerification $verification Once expired
     * @param string $clearTextToken Once valid, once invalid
     */
    public function testRegisterUser_invalidExpiredToken(UserVerification $verification, string $clearTextToken): void
    {
        // Return valid verification object from repository
        $this->mock(UserVerificationRepository::class)->expects(self::once())->method(
            'findUserVerification'
        )->willReturn($verification);
        // Return active user (empty user, only status is populated)
        $this->mock(UserRepository::class)->expects(self::once())->method('findUserById')->willReturn(
        // User has to be unverified as this is the default value and its not purpose of this test
            new User(['status' => User::STATUS_UNVERIFIED])
        );

        $this->expectException(InvalidTokenException::class);
        $this->expectExceptionMessage('Invalid or expired token.');

        // Call function under test
        $this->container->get(AuthService::class)->verifyUser($verification->id, $clearTextToken);
    }
}
