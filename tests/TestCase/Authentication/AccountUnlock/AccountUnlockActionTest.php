<?php

namespace App\Test\TestCase\Authentication\AccountUnlock;

use App\Module\Authentication\Data\UserVerificationData;
use App\Module\User\Enum\UserStatus;
use App\Test\Fixture\UserFixture;
use App\Test\TestCase\Authentication\Provider\UserVerificationProvider;
use App\Test\Trait\AppTestTrait;
use Fig\Http\Message\StatusCodeInterface;
use Odan\Session\SessionInterface;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use Slim\Exception\HttpBadRequestException;
use TestTraits\Trait\DatabaseTestTrait;
use TestTraits\Trait\FixtureTestTrait;
use TestTraits\Trait\HttpTestTrait;
use TestTraits\Trait\MailerTestTrait;
use TestTraits\Trait\RouteTestTrait;

/**
 * Test that the link sent to a locked user to unblock his account
 * works correctly and safely. Covered in this test:
 *  - Unlock account with valid token with redirect (status active, redirect, auto login)
 *  - Attempt to unlock account with used, invalid and expired token.
 *  - Attempt to unlock account that is already active.
 */
class AccountUnlockActionTest extends TestCase
{
    use AppTestTrait;
    use HttpTestTrait;
    use DatabaseTestTrait;
    use RouteTestTrait;
    use MailerTestTrait;
    use FixtureTestTrait;

    /**
     * Test that with given correct token the account status is set to active.
     */
    #[DataProviderExternal(UserVerificationProvider::class, 'userVerificationProvider')]
    public function testAccountUnlockAction(UserVerificationData $verification, string $clearTextToken): void
    {
        // Insert locked user
        $userRow = $this->insertFixture(
            UserFixture::class,
            ['status' => UserStatus::Locked->value, 'id' => $verification->userId],
        );

        $this->insertFixtureRow('user_verification', $verification->toArrayForDatabase());

        // Test redirect at the same time
        $redirectLocation = $this->urlFor('client-list-page');
        $queryParams = [
            'redirect' => $redirectLocation,
            'token' => $clearTextToken,
            'id' => (string)$verification->id,
        ];

        $request = $this->createRequest('GET', $this->urlFor('account-unlock-verification', [], $queryParams));
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
     * Test that with given used, invalid or expired token the account cannot be unlocked
     * This is a very important test for security.
     */
    #[DataProviderExternal(UserVerificationProvider::class, 'userVerificationInvalidTokenProvider')]
    public function testAccountUnlockActionInvalidExpiredToken(
        UserVerificationData $verification,
        string $clearTextToken,
    ): void {
        // Insert locked user
        $userRow = $this->insertFixture(
            UserFixture::class,
            ['status' => UserStatus::Locked->value, 'id' => $verification->userId],
        );

        $this->insertFixtureRow('user_verification', $verification->toArrayForDatabase());

        // Test redirect at the same time
        $redirectLocation = $this->urlFor('client-list-page');
        $queryParams = [
            'redirect' => $redirectLocation,
            'token' => $clearTextToken,
            'id' => (string)$verification->id,
        ];

        $request = $this->createRequest('GET', $this->urlFor('account-unlock-verification', [], $queryParams));
        $response = $this->app->handle($request);

        // Assert that redirect to register worked with correct further redirect query params
        $loginPage = $this->urlFor('login-page', [], ['redirect' => $redirectLocation]);
        self::assertSame($loginPage, $response->getHeaderLine('Location'));
        self::assertSame(StatusCodeInterface::STATUS_FOUND, $response->getStatusCode());

        // Assert that token had NOT been used (except if already used)
        self::assertSame(
            $verification->usedAt,
            $this->getTableRowById('user_verification', (int)$verification->id, ['used_at'])['used_at']
        );
        // Assert that status is still locked on user
        $this->assertTableRowValue(UserStatus::Locked->value, 'user', $userRow['id'], 'status');

        $session = $this->container->get(SessionInterface::class);
        // Assert that session user_id is null meaning user is NOT logged-in
        self::assertNull($session->get('user_id'));
    }

    /**
     * Test that if the user has status already on active he gets redirected
     * but not authenticated.
     */
    #[DataProviderExternal(UserVerificationProvider::class, 'userVerificationProvider')]
    public function testAccountUnlockActionAlreadyUnlocked(
        UserVerificationData $verification,
        string $clearTextToken,
    ): void {
        // Insert locked user
        $userRow = $this->insertFixture(
            UserFixture::class,
            ['status' => UserStatus::Active->value, 'id' => $verification->userId],
        );

        $this->insertFixtureRow('user_verification', $verification->toArrayForDatabase());

        // Test redirect at the same time
        $redirectLocation = $this->urlFor('client-list-page');
        $queryParams = [
            'redirect' => $redirectLocation,
            'token' => $clearTextToken,
            'id' => (string)$verification->id,
        ];

        $request = $this->createRequest('GET', $this->urlFor('account-unlock-verification', [], $queryParams));
        $response = $this->app->handle($request);

        // Assert that redirect worked

        self::assertSame(
            $this->urlFor('login-page', [], ['redirect' => $redirectLocation]),
            $response->getHeaderLine('Location')
        );
        self::assertSame(StatusCodeInterface::STATUS_FOUND, $response->getStatusCode());

        $session = $this->container->get(SessionInterface::class);
        // Assert that user is not logged in (would also make sense to auth user if unlock token is valid)
        self::assertNull($session->get('user_id'));
    }

    /**
     * Test that correct error is thrown if request body is malformed.
     */
    public function testAccountUnlockActionMalformedBody(): void
    {
        $request = $this->createRequest('GET', $this->urlFor('account-unlock-verification'));

        // Bad Request (400) means that the client sent the request wrongly; it's a client error
        $this->expectException(HttpBadRequestException::class);
        $this->expectExceptionMessage('Query params malformed.');

        $response = $this->app->handle($request);
    }
}
