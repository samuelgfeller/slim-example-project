<?php

namespace App\Test\Integration\Authentication;

use App\Domain\Authentication\Data\UserVerificationData;
use App\Domain\User\Enum\UserStatus;
use App\Test\Fixture\UserFixture;
use App\Test\Trait\AppTestTrait;
use Fig\Http\Message\StatusCodeInterface;
use Odan\Session\SessionInterface;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use Slim\Exception\HttpBadRequestException;
use TestTraits\Trait\DatabaseTestTrait;
use TestTraits\Trait\FixtureTestTrait;
use TestTraits\Trait\HttpTestTrait;
use TestTraits\Trait\RouteTestTrait;

/**
 * Test that the link sent to user when creating an account
 * works correctly and safely. Test covered in this class:
 *  - Set user account status from unverified to active with valid token
 *  - Attempt to verify user account that is already active
 *  - Attempt to verify user with used, invalid and expired token
 *  - Test action with invalid parameters (400 Bad request).
 */
class RegisterVerifyActionTest extends TestCase
{
    use AppTestTrait;
    use DatabaseTestTrait;
    use HttpTestTrait;
    use RouteTestTrait;
    use FixtureTestTrait;

    /**
     * Test that with given correct token the account status is set to active.
     *
     * @param UserVerificationData $verification
     * @param string $clearTextToken
     */
    #[DataProviderExternal(\App\Test\Provider\Authentication\UserVerificationProvider::class, 'userVerificationProvider')]
    public function testRegisterVerification(UserVerificationData $verification, string $clearTextToken): void
    {
        // User needed to insert verification (taking first record from userFixture)
        $userRow = $this->insertFixture(
            UserFixture::class,
            ['id' => $verification->userId, 'status' => UserStatus::Unverified->value],
        );

        $this->insertFixtureRow('user_verification', $verification->toArrayForDatabase());

        $redirectLocation = $this->urlFor('user-list');
        $queryParams = [
            // Test redirect at the same time
            'redirect' => $redirectLocation,
            'token' => $clearTextToken,
            'id' => (string)$verification->id,
        ];

        $request = $this->createRequest('GET', $this->urlFor('register-verification', [], $queryParams));
        $response = $this->app->handle($request);

        // Assert that redirect worked
        self::assertSame($redirectLocation, $response->getHeaderLine('Location'));
        self::assertSame(StatusCodeInterface::STATUS_FOUND, $response->getStatusCode());

        // Assert that token has been used
        self::assertNotNull(
            $this->getTableRowById('user_verification', (int)$verification->id, ['used_at'])['used_at']
        );

        // Assert that status is active on user
        $this->assertTableRowValue(UserStatus::Active->value, 'user', $userRow['id'], 'status');

        $session = $this->container->get(SessionInterface::class);
        // Assert that session user_id is set meaning user is logged-in
        self::assertNotNull($session->get('user_id'));
    }

    /**
     * Check that user gets redirect to the home or wanted page and most importantly: that no error is thrown.
     *
     * @param UserVerificationData $verification
     * @param string $clearTextToken
     */
    #[DataProviderExternal(\App\Test\Provider\Authentication\UserVerificationProvider::class, 'userVerificationProvider')]
    public function testRegisterVerificationAlreadyVerified(
        UserVerificationData $verification,
        string $clearTextToken
    ): void {
        // User needed to insert verification
        $userRow = $this->insertFixture(
            UserFixture::class,
            ['id' => $verification->userId, 'status' => UserStatus::Active->value],
        );

        $this->insertFixtureRow('user_verification', $verification->toArrayForDatabase());
        // Any location to test that page that user visited before is in the redirect param
        $redirectLocation = $this->urlFor('profile-page');
        $queryParams = [
            // Test redirect at the same time
            'redirect' => $redirectLocation,
            'token' => $clearTextToken,
            'id' => (string)$verification->id,
        ];

        $request = $this->createRequest('GET', $this->urlFor('register-verification', [], $queryParams));

        $response = $this->app->handle($request);

        // Assert that redirect worked when not logged in. When authenticated, redirect should go to location directly
        self::assertSame(
            $this->urlFor('login-page', [], ['redirect' => $redirectLocation]),
            $response->getHeaderLine('Location')
        );
        self::assertSame(StatusCodeInterface::STATUS_FOUND, $response->getStatusCode());
        // Here it's important that no exception is thrown when user is already verified. There is only a flash info.

        // Assert that info flash message is set that informs user that they are already verified
        $flash = $this->container->get(SessionInterface::class)->getFlash()->all();
        self::assertSame('You are already verified. Please log in.', $flash['info'][0]);

        // Assert that user is NOT authenticated
        $session = $this->container->get(SessionInterface::class);
        self::assertNull($session->get('user_id'));
    }

    #[DataProviderExternal(\App\Test\Provider\Authentication\UserVerificationProvider::class, 'userVerificationProvider')]
    public function testRegisterVerificationAlreadyVerifiedAndAuthenticated(
        UserVerificationData $verification,
        string $clearTextToken
    ): void {
        // User needed to insert verification
        $userRow = $this->insertFixture(
            UserFixture::class,
            ['id' => $verification->userId, 'status' => UserStatus::Active->value],
        );
        // Insert user verification
        $this->insertFixtureRow('user_verification', $verification->toArrayForDatabase());
        // Test redirect param to this page
        $redirectLocation = $this->urlFor('profile-page');
        $queryParams = [
            // To test redirect
            'redirect' => $redirectLocation,
            'token' => $clearTextToken,
            'id' => (string)$verification->id,
        ];

        // Authenticate user
        $this->container->get(SessionInterface::class)->set('user_id', $userRow['id']);

        $request = $this->createRequest('GET', $this->urlFor('register-verification', [], $queryParams));

        $response = $this->app->handle($request);

        // Assert that redirect worked when logged in
        self::assertSame($redirectLocation, $response->getHeaderLine('Location'));
        self::assertSame(StatusCodeInterface::STATUS_FOUND, $response->getStatusCode());

        // Assert that info flash message is set that informs user that they are already logged in
        $flash = $this->container->get(SessionInterface::class)->getFlash()->all();
        self::assertStringContainsString('You are already logged in', $flash['info'][0]);
    }

    /**
     * Link in email contains the verification db entry id and if this id is incorrect (token not found)
     * according exception should be thrown and user redirected to register page.
     *
     * @param UserVerificationData $verification
     * @param string $clearTextToken
     */
    #[DataProviderExternal(\App\Test\Provider\Authentication\UserVerificationProvider::class, 'userVerificationInvalidTokenProvider')]
    public function testRegisterVerificationInvalidUsedExpiredToken(
        UserVerificationData $verification,
        string $clearTextToken
    ): void {
        // User needed to insert verification
        $userRow = $this->insertFixture(
            UserFixture::class,
            ['id' => $verification->userId, 'status' => UserStatus::Unverified->value],
        );

        $this->insertFixtureRow('user_verification', $verification->toArrayForDatabase());

        $redirectLocation = $this->urlFor('user-list-page');
        $queryParams = [
            // Test redirect at the same time
            'redirect' => $redirectLocation,
            'token' => $clearTextToken,
            'id' => (string)$verification->id,
        ];

        $request = $this->createRequest('GET', $this->urlFor('register-verification', [], $queryParams));
        $response = $this->app->handle($request);

        // Assert that client is redirected to register page with the redirect GET param
        // because he/she has to register again to get a new token
        self::assertSame(
            $this->urlFor('login-page', [], ['redirect' => $redirectLocation]),
            $response->getHeaderLine('Location')
        );
        self::assertSame(StatusCodeInterface::STATUS_FOUND, $response->getStatusCode());

        // Assert that token had NOT been used (except if already used)
        self::assertSame(
            $verification->usedAt,
            $this->getTableRowById('user_verification', (int)$verification->id, ['used_at'])['used_at']
        );

        // Assert that status is still unverified on user
        $this->assertTableRowValue(UserStatus::Unverified->value, 'user', $userRow['id'], 'status');

        $session = $this->container->get(SessionInterface::class);
        // Assert that session user_id is not set
        self::assertNull($session->get('user_id'));
    }

    /**
     * Test that exception is thrown when request body is malformed.
     */
    public function testRegisterVerificationBadRequest(): void
    {
        // No need to insert anything as exception should be thrown immediately

        $queryParams = [
            // Missing token
            'id' => '1',
        ];

        $request = $this->createRequest('GET', $this->urlFor('register-verification', [], $queryParams));

        $this->expectException(HttpBadRequestException::class);

        $this->app->handle($request);
    }
}
