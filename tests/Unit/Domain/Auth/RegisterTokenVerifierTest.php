<?php

namespace App\Test\Unit\Domain\Auth;

use App\Domain\Auth\DTO\UserVerification;
use App\Domain\Auth\Exception\InvalidTokenException;
use App\Domain\Auth\Exception\UserAlreadyVerifiedException;
use App\Domain\Auth\Service\RegisterTokenVerifier;
use App\Domain\User\DTO\User;
use App\Infrastructure\User\UserRepository;
use App\Infrastructure\User\UserVerificationRepository;
use App\Test\AppTestTrait;
use PHPUnit\Framework\TestCase;

/**
 * Email verification test (after user clicked on link)
 */
class RegisterTokenVerifierTest extends TestCase
{
    use AppTestTrait;

    /**
     * Test that with valid values all security checks pass until changeUserStatus is called
     *
     * @dataProvider \App\Test\Provider\UserVerificationProvider::userVerificationProvider
     * @param UserVerification $verification
     * @param string $clearTextToken
     */
    public function testGetUserIdIfTokenIsValid(UserVerification $verification, string $clearTextToken): void
    {
        // Create mocks
        $userVerificationRepository = $this->mock(UserVerificationRepository::class);
        $userRepository = $this->mock(UserRepository::class);

        // Return valid verification object from repository
        $userVerificationRepository->method('findUserVerification')->willReturn($verification);
        // Set user id that should be returned by the function under test for a success
        $userVerificationRepository->method('getUserIdFromVerification')->willReturn(1);

        // Return unverified user (empty user, only status is populated)
        $userRepository->expects(self::once())->method('findUserById')->willReturn(
        // IMPORTANT: user has to be unverified for the test to succeed
            new User(['status' => User::STATUS_UNVERIFIED])
        );
        // Making sure that changeUserStatus is called
        $userRepository->expects(self::once())->method('changeUserStatus')->willReturn(true);
        // Assert that setVerificationEntryToUsed is called
        $userVerificationRepository->expects(self::once())->method('setVerificationEntryToUsed')->willReturn(true);

        $tokenVerifier = $this->container->get(RegisterTokenVerifier::class);
        // Call function under test
        self::assertSame(1, $tokenVerifier->getUserIdIfTokenIsValid($verification->id, $clearTextToken));
    }

    /**
     * Case when user clicks on the link even though the user is not 'unverified' anymore
     *
     * @dataProvider \App\Test\Provider\UserVerificationProvider::userVerificationProvider
     * @param UserVerification $verification
     * @param string $clearTextToken
     */
    public function testGetUserIdIfTokenIsValid_alreadyVerified(
        UserVerification $verification,
        string $clearTextToken
    ): void {
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
        $this->container->get(RegisterTokenVerifier::class)->getUserIdIfTokenIsValid(
            $verification->id,
            $clearTextToken
        );
    }

    /**
     * Link in email contains the verification db entry id and if this id is incorrect (token not found)
     * according exception should be thrown
     */
    public function testGetUserIdIfTokenIsValid_notExistingToken(): void
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
        $this->container->get(RegisterTokenVerifier::class)->getUserIdIfTokenIsValid(
            $verificationId,
            'wrongTokenButItDoesntMatter'
        );
    }

    /**
     * Test when token is invalid or expired
     *
     * Provider gives once an invalid token and once an expired one
     * @dataProvider \App\Test\Provider\UserVerificationProvider::userVerificationInvalidExpiredProvider
     *
     * @param UserVerification $verification Once expired
     * @param string $clearTextToken Once valid, once invalid
     */
    public function testGetUserIdIfTokenIsValid_invalidExpiredToken(
        UserVerification $verification,
        string $clearTextToken
    ): void {
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
        $this->container->get(RegisterTokenVerifier::class)->getUserIdIfTokenIsValid(
            $verification->id,
            $clearTextToken
        );
    }
}
