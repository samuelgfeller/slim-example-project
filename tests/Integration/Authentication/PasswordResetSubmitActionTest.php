<?php

namespace App\Test\Integration\Authentication;

use App\Domain\Authentication\Data\UserVerificationData;
use App\Domain\User\Enum\UserStatus;
use App\Test\Fixture\UserFixture;
use App\Test\Provider\Authentication\UserVerificationProvider;
use App\Test\Trait\AppTestTrait;
use Fig\Http\Message\StatusCodeInterface;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use TestTraits\Trait\DatabaseTestTrait;
use TestTraits\Trait\FixtureTestTrait;
use TestTraits\Trait\HttpJsonTestTrait;
use TestTraits\Trait\HttpTestTrait;
use TestTraits\Trait\RouteTestTrait;

/**
 * Integration testing password change from authenticated user
 *  - request to set new password with valid token
 *  - request to set new password with invalid, expired and used token
 *  - request to set new password with valid token but invalid password (too short)
 *  - request to set new password with malformed request body -> bad request.
 */
class PasswordResetSubmitActionTest extends TestCase
{
    use AppTestTrait;
    use HttpTestTrait;
    use HttpJsonTestTrait;
    use RouteTestTrait;
    use DatabaseTestTrait;
    use FixtureTestTrait;

    /**
     * Request to reset password with token.
     *
     * @param UserVerificationData $verification
     * @param string $clearTextToken
     */
    #[DataProviderExternal(UserVerificationProvider::class, 'userVerificationProvider')]
    #[DataProviderExternal(UserVerificationProvider::class, 'userVerificationProvider')]
    public function testResetPasswordSubmit(UserVerificationData $verification, string $clearTextToken): void
    {
        $newPassword = 'new password';
        // Insert user
        $userRow = $this->insertFixture(UserFixture::class, ['id' => $verification->userId]);

        $this->insertFixtureRow('user_verification', $verification->toArrayForDatabase());

        $request = $this->createFormRequest(
            'POST', // Request to change password
            $this->urlFor('password-reset-submit'),
            [
                'password' => $newPassword,
                'password2' => $newPassword,
                'token' => $clearTextToken,
                'id' => (string)$verification->id,
            ]
        );

        $response = $this->app->handle($request);

        // Assert: 302 Redirect to login page
        self::assertSame(StatusCodeInterface::STATUS_FOUND, $response->getStatusCode());
        self::assertSame($this->urlFor('login-page'), $response->getHeaderLine('Location'));

        // Assert that password was changed correctly
        $dbPasswordHash = $this->getTableRowById('user', $userRow['id'])['password_hash'];

        // Verify that hash matches the given password
        self::assertTrue(password_verify($newPassword, $dbPasswordHash));
    }

    /**
     * Test password submit reset with invalid, used or expired token.
     *
     * @param UserVerificationData $verification
     * @param string $clearTextToken
     */
    #[DataProviderExternal(UserVerificationProvider::class, 'userVerificationInvalidTokenProvider')]
    public function testResetPasswordSubmitInvalidToken(
        UserVerificationData $verification,
        string $clearTextToken,
    ): void {
        // User needed to insert verification
        $userRow = $this->insertFixture(
            UserFixture::class,
            ['id' => $verification->userId, 'status' => UserStatus::Unverified->value],
        );

        $this->insertFixtureRow('user_verification', $verification->toArrayForDatabase());
        $newPassword = 'new password';
        $request = $this->createFormRequest(
            'POST', // Request to change password
            $this->urlFor('password-reset-submit'),
            [
                'password' => $newPassword,
                'password2' => $newPassword,
                'token' => $clearTextToken,
                'id' => (string)$verification->id,
            ]
        );

        $response = $this->app->handle($request);

        // Assert 200 OK password reset first email form loaded
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        // Assert that token had NOT been used (except if already used)
        $this->assertTableRowValue($verification->usedAt, 'user_verification', (int)$verification->id, 'used_at');

        // Assert that the password was not changed to the new one
        $this->assertTableRowValue(UserStatus::Unverified->value, 'user', $userRow['id'], 'status');

        // Assert that password was NOT changed
        $dbPasswordHash = $this->getTableRowById('user', $userRow['id'])['password_hash'];
        self::assertFalse(password_verify($newPassword, $dbPasswordHash));

        // Get response body as string from stream
        $stream = $response->getBody();
        $stream->rewind();
        $body = $stream->getContents();

        // Assert that response body contains validation error
        self::assertStringContainsString('Invalid, used or expired link', $body);
    }

    /**
     * Test that backend validation fails when new passwords are invalid.
     *
     * @param UserVerificationData $verification
     * @param string $clearTextToken
     */
    #[DataProviderExternal(UserVerificationProvider::class, 'userVerificationProvider')]
    public function testResetPasswordSubmitInvalidData(
        UserVerificationData $verification,
        string $clearTextToken,
    ): void {
        // Insert user id 2 role: user
        $userRow = $this->insertFixture(UserFixture::class, ['id' => $verification->userId]);

        $this->insertFixtureRow('user_verification', $verification->toArrayForDatabase());

        $request = $this->createFormRequest(
            'POST', // Request to change password
            $this->urlFor('password-reset-submit'),
            [
                // Password too short
                'password' => '1',
                'password2' => '1',
                'token' => $clearTextToken,
                'id' => (string)$verification->id,
            ]
        );

        $response = $this->app->handle($request);

        // Assert that response has error status 422
        self::assertSame(StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY, $response->getStatusCode());

        // Get response body as string from stream
        $stream = $response->getBody();
        $stream->rewind();
        $body = $stream->getContents();

        // Assert that response body contains validation error
        self::assertStringContainsString('Minimum length is 3', $body);
    }

    /**
     * Test that password reset page loads successfully.
     *
     * @return void
     */
    public function testPasswordResetPageAction(): void
    {
        $request = $this->createRequest(
            'GET',
            $this->urlFor(
                'password-reset-page',
                [],
                ['id' => '1', 'token' => 'Token Content Does Not Matter As Only Key Is Checked In Page Action']
            ),
        );
        $response = $this->app->handle($request);
        // Assert 200 OK
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
    }

    /**
     * Test that password reset page loads with status code 400 if the token is missing.
     *
     * @return void
     */
    public function testPasswordResetPageActionTokenMissing(): void
    {
        // Create token with missing token
        $request = $this->createRequest('GET', $this->urlFor('password-reset-page'));
        $response = $this->app->handle($request);
        // Assert 400 Bad request
        self::assertSame(StatusCodeInterface::STATUS_BAD_REQUEST, $response->getStatusCode());
    }
}
