<?php

namespace App\Test\Integration\Authentication;

use App\Domain\Authentication\Data\UserVerificationData;
use App\Domain\User\Enum\UserStatus;
use App\Test\Fixture\UserFixture;
use App\Test\Traits\AppTestTrait;
use App\Test\Traits\FixtureTestTrait;
use Fig\Http\Message\StatusCodeInterface;
use Odan\Session\SessionInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Selective\TestTrait\Traits\DatabaseTestTrait;
use Selective\TestTrait\Traits\MailerTestTrait;
use Selective\TestTrait\Traits\RouteTestTrait;

/**
 * Test that the link sent to a locked user to unblock his account
 * works correctly and safely. Covered in this test:
 *  - Unlock account with valid token with redirect (status active, redirect, auto login)
 *  - Attempt to unlock account with used, invalid and expired token.
 */
class AccountUnlockActionTest extends TestCase
{
    use AppTestTrait;
    use DatabaseTestTrait;
    use RouteTestTrait;
    use MailerTestTrait;
    use FixtureTestTrait;

    /**
     * Test that with given correct token the account status is set to active.
     *
     * @dataProvider \App\Test\Provider\Authentication\UserVerificationProvider::userVerificationProvider()
     *
     * @param UserVerificationData $verification
     * @param string $clearTextToken
     */
    public function testAccountUnlockAction(UserVerificationData $verification, string $clearTextToken): void
    {
        // Insert locked user
        $userRow = $this->insertFixturesWithAttributes(
            ['status' => UserStatus::Locked->value, 'id' => $verification->userId],
            UserFixture::class
        );

        $this->insertFixture('user_verification', $verification->toArrayForDatabase());

        // Test redirect at the same time
        $redirectLocation = $this->urlFor('client-list-page');
        $queryParams = [
            'redirect' => $redirectLocation,
            'token' => $clearTextToken,
            'id' => $verification->id,
        ];

        $request = $this->createRequest('GET', $this->urlFor('account-unlock-verification', [], $queryParams))
            // Needed until nyholm/psr7 supports ->getQueryParams() taking uri query parameters if no other are set [SLE-105]
            ->withQueryParams($queryParams);
        $response = $this->app->handle($request);

        // Assert that redirect worked
        self::assertSame($redirectLocation, $response->getHeaderLine('Location'));
        self::assertSame(StatusCodeInterface::STATUS_FOUND, $response->getStatusCode());

        // Assert that token has been used
        self::assertNotNull($this->getTableRowById('user_verification', $verification->id, ['used_at'])['used_at']);

        // Assert that status is active on user
        $this->assertTableRowValue(UserStatus::Active->value, 'user', $userRow['id'], 'status');

        $session = $this->container->get(SessionInterface::class);
        // Assert that session user_id is set meaning user is logged-in
        self::assertNotNull($session->get('user_id'));
    }

    /**
     * Test that with given used, invalid or expired token the account cannot be unlocked
     * This is a very important test for security.
     *
     * @dataProvider \App\Test\Provider\Authentication\UserVerificationProvider::userVerificationInvalidExpiredProvider()
     *
     * @param UserVerificationData $verification
     * @param string $clearTextToken
     */
    public function testAccountUnlockActionInvalidExpiredToken(
        UserVerificationData $verification,
        string $clearTextToken
    ): void {
        // Insert locked user
        $userRow = $this->insertFixturesWithAttributes(
            ['status' => UserStatus::Locked->value, 'id' => $verification->userId],
            UserFixture::class
        );

        $this->insertFixture('user_verification', $verification->toArrayForDatabase());

        // Test redirect at the same time
        $redirectLocation = $this->urlFor('client-list-page');
        $queryParams = [
            'redirect' => $redirectLocation,
            'token' => $clearTextToken,
            'id' => $verification->id,
        ];

        $request = $this->createRequest('GET', $this->urlFor('account-unlock-verification', [], $queryParams))
            // Needed until nyholm/psr7 supports ->getQueryParams() taking uri query parameters if no other are set [SLE-105]
            ->withQueryParams($queryParams);
        $response = $this->app->handle($request);

        // Assert that redirect to register worked with correct further redirect query params
        $loginPage = $this->urlFor('login-page', [], ['redirect' => $redirectLocation]);
        self::assertSame($loginPage, $response->getHeaderLine('Location'));
        self::assertSame(StatusCodeInterface::STATUS_FOUND, $response->getStatusCode());

        // Assert that token had NOT been used (except if already used)
        self::assertSame(
            $verification->usedAt,
            $this->getTableRowById('user_verification', $verification->id, ['used_at'])['used_at']
        );
        // Assert that status is still locked on user
        $this->assertTableRowValue(UserStatus::Locked->value, 'user', $userRow['id'], 'status');

        $session = $this->container->get(SessionInterface::class);
        // Assert that session user_id is null meaning user is NOT logged-in
        self::assertNull($session->get('user_id'));
    }

    /**
     * Test that if user has status already on active he gets redirected
     * but not authenticated.
     *
     * @dataProvider \App\Test\Provider\Authentication\UserVerificationProvider::userVerificationProvider()
     *
     * @param UserVerificationData $verification
     * @param string $clearTextToken
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testAccountUnlockActionAlreadyUnlocked(
        UserVerificationData $verification,
        string $clearTextToken
    ): void {
        // Insert locked user
        $userRow = $this->insertFixturesWithAttributes(
            ['status' => UserStatus::Active->value, 'id' => $verification->userId],
            UserFixture::class
        );

        $this->insertFixture('user_verification', $verification->toArrayForDatabase());

        // Test redirect at the same time
        $redirectLocation = $this->urlFor('client-list-page');
        $queryParams = [
            'redirect' => $redirectLocation,
            'token' => $clearTextToken,
            'id' => $verification->id,
        ];

        $request = $this->createRequest('GET', $this->urlFor('account-unlock-verification', [], $queryParams))
            // Needed until nyholm/psr7 supports ->getQueryParams() taking uri query parameters if no other are set [SLE-105]
            ->withQueryParams($queryParams);
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
}
