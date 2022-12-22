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
use Selective\TestTrait\Traits\DatabaseTestTrait;
use Selective\TestTrait\Traits\HttpTestTrait;
use Selective\TestTrait\Traits\RouteTestTrait;
use Slim\Exception\HttpBadRequestException;

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
     * @dataProvider \App\Test\Provider\Authentication\UserVerificationProvider::userVerificationProvider()
     *
     * @param UserVerificationData $verification
     * @param string $clearTextToken
     */
    public function testRegisterVerification(UserVerificationData $verification, string $clearTextToken): void
    {
        // User needed to insert verification (taking first record from userFixture)
        $userRow = $this->insertFixturesWithAttributes(
            ['id' => $verification->userId, 'status' => UserStatus::Unverified->value],
            UserFixture::class
        );

        $this->insertFixture('user_verification', $verification->toArrayForDatabase());

        $redirectLocation = $this->urlFor('user-list');
        $queryParams = [
            // Test redirect at the same time
            'redirect' => $redirectLocation,
            'token' => $clearTextToken,
            'id' => $verification->id,
        ];

        $request = $this->createRequest('GET', $this->urlFor('register-verification', [], $queryParams))
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
     * Check that user gets redirect to the home or wanted page and most importantly: that no error is thrown.
     *
     * @dataProvider \App\Test\Provider\Authentication\UserVerificationProvider::userVerificationProvider()
     *
     * @param UserVerificationData $verification
     * @param string $clearTextToken
     */
    public function testRegisterVerificationAlreadyVerified(
        UserVerificationData $verification,
        string $clearTextToken
    ): void {
        // User needed to insert verification
        $userRow = $this->insertFixturesWithAttributes(
            ['id' => $verification->userId, 'status' => UserStatus::Active->value],
            UserFixture::class
        );

        $this->insertFixture('user_verification', $verification->toArrayForDatabase());
        // Any location to test that page that user visited before is in the redirect param
        $redirectLocation = $this->urlFor('profile-page');
        $queryParams = [
            // Test redirect at the same time
            'redirect' => $redirectLocation,
            'token' => $clearTextToken,
            'id' => $verification->id,
        ];

        $request = $this->createRequest('GET', $this->urlFor('register-verification', [], $queryParams))
            // Needed until Nyholm/psr7 supports ->getQueryParams() taking uri query parameters if no other are set [SLE-105];
            ->withQueryParams($queryParams);

        $response = $this->app->handle($request);

        // Assert that redirect worked when not logged in. When authenticated, redirect should go to location directly
        self::assertSame(
            $this->urlFor('login-page', [], ['redirect' => $redirectLocation]),
            $response->getHeaderLine('Location')
        );
        self::assertSame(StatusCodeInterface::STATUS_FOUND, $response->getStatusCode());
        // Here it's important that no exception is thrown when user is already verified. There is only a flash info.

        // Assert that user is NOT authenticated
        $session = $this->container->get(SessionInterface::class);
        self::assertNull($session->get('user_id'));
    }

    /**
     * Link in email contains the verification db entry id and if this id is incorrect (token not found)
     * according exception should be thrown and user redirected to register page.
     *
     * @dataProvider \App\Test\Provider\Authentication\UserVerificationProvider::userVerificationInvalidExpiredProvider()
     *
     * @param UserVerificationData $verification
     * @param string $clearTextToken
     */
    public function testRegisterVerificationInvalidUsedExpiredToken(
        UserVerificationData $verification,
        string $clearTextToken
    ): void {
        // User needed to insert verification
        $userRow = $this->insertFixturesWithAttributes(
            ['id' => $verification->userId, 'status' => UserStatus::Unverified->value],
            UserFixture::class
        );

        $this->insertFixture('user_verification', $verification->toArrayForDatabase());

        $redirectLocation = $this->urlFor('user-list-page');
        $queryParams = [
            // Test redirect at the same time
            'redirect' => $redirectLocation,
            'token' => $clearTextToken,
            'id' => $verification->id,
        ];

        $request = $this->createRequest('GET', $this->urlFor('register-verification', [], $queryParams))
            // Needed until Nyholm/psr7 supports ->getQueryParams() taking uri query parameters if no other are set [SLE-105]
            ->withQueryParams($queryParams);
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
            $this->getTableRowById('user_verification', $verification->id, ['used_at'])['used_at']
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
            'id' => 1,
        ];

        $request = $this->createRequest('GET', $this->urlFor('register-verification', [], $queryParams))
            // Needed until Nyholm/psr7 supports ->getQueryParams() taking uri query parameters if no other are set [SLE-105];
            ->withQueryParams($queryParams);

        $this->expectException(HttpBadRequestException::class);

        $this->app->handle($request);
    }
}
