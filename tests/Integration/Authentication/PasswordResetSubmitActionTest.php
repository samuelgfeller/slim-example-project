<?php

namespace App\Test\Integration\Authentication;

use App\Domain\Authentication\Data\UserVerificationData;
use App\Domain\User\Enum\UserStatus;
use App\Test\Fixture\UserFixture;
use App\Test\Traits\AppTestTrait;
use App\Test\Traits\FixtureTestTrait;
use Fig\Http\Message\StatusCodeInterface;
use PHPUnit\Framework\TestCase;
use Selective\TestTrait\Traits\DatabaseTestTrait;
use Selective\TestTrait\Traits\HttpJsonTestTrait;
use Selective\TestTrait\Traits\HttpTestTrait;
use Selective\TestTrait\Traits\RouteTestTrait;

/**
 * Integration testing password change from authenticated user
 *  - request to set new password with valid token
 *  - request to set new password with invalid, expired and used token -> redirect to login page
 *  - request to set new password with valid token but invalid data -> 400 Bad request
 *  - request to set new password with malformed request body -> HttpBadRequestException.
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
     * @dataProvider \App\Test\Provider\Authentication\UserVerificationProvider::userVerificationProvider()
     *
     * @param UserVerificationData $verification
     * @param string $clearTextToken
     */
    public function testResetPasswordSubmit(UserVerificationData $verification, string $clearTextToken): void
    {
        $newPassword = 'new password';
        // Insert user
        $userRow = $this->insertFixturesWithAttributes(['id' => $verification->userId], UserFixture::class);

        $this->insertFixture('user_verification', $verification->toArrayForDatabase());

        $request = $this->createFormRequest(
            'POST', // Request to change password
            $this->urlFor('password-reset-submit'),
            [
                'password' => $newPassword,
                'password2' => $newPassword,
                'token' => $clearTextToken,
                'id' => $verification->id,
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
     * @dataProvider \App\Test\Provider\Authentication\UserVerificationProvider::userVerificationInvalidTokenProvider()
     *
     * @param UserVerificationData $verification
     * @param string $clearTextToken
     */
    public function testResetPasswordSubmitInvalidToken(
        UserVerificationData $verification,
        string $clearTextToken
    ): void {
        // User needed to insert verification
        $userRow = $this->insertFixturesWithAttributes(
            ['id' => $verification->userId, 'status' => UserStatus::Unverified->value],
            UserFixture::class
        );

        $this->insertFixture('user_verification', $verification->toArrayForDatabase());
        $newPassword = 'new password';
        $request = $this->createFormRequest(
            'POST', // Request to change password
            $this->urlFor('password-reset-submit'),
            [
                'password' => $newPassword,
                'password2' => $newPassword,
                'token' => $clearTextToken,
                'id' => $verification->id,
            ]
        );

        $response = $this->app->handle($request);

        // Assert 200 password reset first email form loaded
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        // Assert that token had NOT been used (except if already used)
        self::assertSame(
            $verification->usedAt,
            $this->getTableRowById('user_verification', $verification->id, ['used_at'])['used_at']
        );

        // Assert that password was not changed to the new one
        $this->assertTableRowValue(UserStatus::Unverified->value, 'user', $userRow['id'], 'status');

        // Assert that password was NOT changed
        $dbPasswordHash = $this->getTableRowById('user', $userRow['id'])['password_hash'];
        self::assertFalse(password_verify($newPassword, $dbPasswordHash));
    }

    /**
     * Test that backend validation fails when new passwords are invalid.
     *
     * @dataProvider \App\Test\Provider\Authentication\UserVerificationProvider::userVerificationProvider()
     *
     * @param UserVerificationData $verification
     * @param string $clearTextToken
     */
    public function testResetPasswordSubmitInvalidData(
        UserVerificationData $verification,
        string $clearTextToken
    ): void {
        // Invalid new password
        $newPassword = '1';
        // Insert user id 2 role: user
        $userRow = $this->insertFixturesWithAttributes(['id' => $verification->userId], UserFixture::class);

        $this->insertFixture('user_verification', $verification->toArrayForDatabase());

        $request = $this->createFormRequest(
            'POST', // Request to change password
            $this->urlFor('password-reset-submit'),
            [
                'password' => $newPassword,
                'password2' => $newPassword,
                'token' => $clearTextToken,
                'id' => $verification->id,
            ]
        );

        $response = $this->app->handle($request);

        // Assert that response has error status 422
        self::assertSame(StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        // As form is directly rendered with validation errors it's not possible to test them as response is a stream
    }

    /**
     * Test that password reset page loads successfully.
     * @return void
     */
    public function testPasswordResetPageAction(): void
    {
        $request = $this->createRequest(
            'GET',
            $this->urlFor(
                'password-reset-page',
                [],
                ['id' => 1, 'token' => 'Token Content Does Not Matter As Only Key Is Checked In Page Action']
            ),
        );
        $response = $this->app->handle($request);
        // Assert 200 OK
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
    }

    /**
     * Test that password reset page loads with status code 400 if token is missing.
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
