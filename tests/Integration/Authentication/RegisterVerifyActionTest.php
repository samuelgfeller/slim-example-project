<?php

namespace App\Test\Integration\Authentication;

use App\Domain\Authentication\Data\UserVerificationData;
use App\Domain\User\Data\UserData;
use App\Test\Traits\AppTestTrait;
use App\Test\Fixture\UserFixture;
use Fig\Http\Message\StatusCodeInterface;
use PHPUnit\Framework\TestCase;
use Selective\TestTrait\Traits\DatabaseTestTrait;
use Selective\TestTrait\Traits\HttpTestTrait;
use App\Test\Traits\RouteTestTrait;
use Slim\Exception\HttpBadRequestException;

class RegisterVerifyActionTest extends TestCase
{
    use AppTestTrait;
    use DatabaseTestTrait;
    use HttpTestTrait;
    use RouteTestTrait;

    /**
     * Test that with given correct token the account status is set to active
     *
     * @dataProvider \App\Test\Provider\Authentication\UserVerificationDataProvider::userVerificationProvider()
     * @param UserVerificationData $verification
     * @param string $clearTextToken
     */
    public function testRegisterVerification(UserVerificationData $verification, string $clearTextToken): void
    {
        // User needed to insert verification (taking first record from userFixture)
        $userRow = (new UserFixture())->records[0];
        $userRow['status'] = UserData::STATUS_UNVERIFIED;
        $this->insertFixture('user', $userRow);

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
        $this->assertTableRowValue(UserData::STATUS_ACTIVE, 'user', $userRow['id'], 'status');
    }

    /**
     * Check that user gets redirect to the home or wanted page and most importantly: that no error is thrown
     *
     * @dataProvider \App\Test\Provider\Authentication\UserVerificationDataProvider::userVerificationProvider()
     * @param UserVerificationData $verification
     * @param string $clearTextToken
     */
    public function testRegisterVerification_alreadyVerified(
        UserVerificationData $verification,
        string $clearTextToken
    ): void {
        // User needed to insert verification
        $userRow = (new UserFixture())->records[0];
        $userRow['status'] = UserData::STATUS_ACTIVE;
        $this->insertFixture('user', $userRow);

        $this->insertFixture('user_verification', $verification->toArrayForDatabase());

        $redirectLocation = $this->urlFor('user-list');
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

        // Assert that redirect worked
        self::assertSame($redirectLocation, $response->getHeaderLine('Location'));
        self::assertSame(StatusCodeInterface::STATUS_FOUND, $response->getStatusCode());
        // Here it's important that no exception is thrown when user is already verified. There is only a flash info.
    }


    /**
     * Link in email contains the verification db entry id and if this id is incorrect (token not found)
     * according exception should be thrown and user redirected to register page
     *
     * @dataProvider \App\Test\Provider\Authentication\UserVerificationDataProvider::userVerificationInvalidExpiredProvider()
     * @param UserVerificationData $verification
     * @param string $clearTextToken
     */
    public function testRegisterVerification_invalidExpiredToken(
        UserVerificationData $verification,
        string $clearTextToken
    ): void {
        // User needed to insert verification
        $userRow = (new UserFixture())->records[0];
        $userRow['status'] = UserData::STATUS_UNVERIFIED;
        $this->insertFixture('user', $userRow);

        $this->insertFixture('user_verification', $verification->toArrayForDatabase());

        $redirectLocation = $this->urlFor('user-list');
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
            $this->urlFor('register-page', [], ['redirect' => $redirectLocation]),
            $response->getHeaderLine('Location')
        );
        self::assertSame(StatusCodeInterface::STATUS_FOUND, $response->getStatusCode());

        // Assert that token had NOT been used
        self::assertNull($this->getTableRowById('user_verification', $verification->id, ['used_at'])['used_at']);

        // Assert that status is still unverified on user
        $this->assertTableRowValue(UserData::STATUS_UNVERIFIED, 'user', $userRow['id'], 'status');
    }

    /**
     * Test that exception is thrown when request body is malformed
     */
    public function testRegisterVerification_badRequest(): void
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
