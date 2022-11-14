<?php

namespace App\Test\Integration\User;

use App\Test\Fixture\FixtureTrait;
use App\Test\Fixture\UserFixture;
use App\Test\Traits\AppTestTrait;
use App\Test\Traits\RouteTestTrait;
use Fig\Http\Message\StatusCodeInterface;
use Odan\Session\SessionInterface;
use PHPUnit\Framework\TestCase;
use Selective\TestTrait\Traits\DatabaseTestTrait;
use Selective\TestTrait\Traits\HttpJsonTestTrait;
use Selective\TestTrait\Traits\HttpTestTrait;
use Slim\Exception\HttpBadRequestException;

/**
 * Integration testing password change from authenticated user
 *  - change password authenticated, correct old password
 *  - change password authenticated, invalid data -> 400 Bad request
 *  - change password not authenticated -> 302 to login page with correct redirect param
 *  - change password authenticated malformed request body -> HttpBadRequestException
 */
class ChangePasswordSubmitActionTest extends TestCase
{

    use AppTestTrait;
    use HttpTestTrait;
    use RouteTestTrait;
    use DatabaseTestTrait;
    use FixtureTrait;
    use HttpJsonTestTrait;

    /**
     * Test user password change with different user roles
     *
     * @dataProvider \App\Test\Provider\User\UserUpdateCaseProvider::provideUserPasswordChangeAuthorizationCases()
     */
    public function testChangePasswordSubmitAction_authorization(
        array $userToUpdateAttr,
        array $authenticatedUserAttr,
        array $expectedResult
    ): void {
        // Insert authenticated user and user to be changed with given attributes containing the user role
        $authenticatedUserRow = $this->insertFixturesWithAttributes($authenticatedUserAttr, UserFixture::class);
        if ($authenticatedUserAttr === $userToUpdateAttr) {
            $userToUpdateRow = $authenticatedUserRow;
        } else {
            // If authenticated user and owner user is not the same, insert owner
            $userToUpdateRow = $this->insertFixturesWithAttributes($userToUpdateAttr, UserFixture::class);
        }

        // Simulate logged-in user
        $this->container->get(SessionInterface::class)->set('user_id', $authenticatedUserRow['id']);

        $oldPassword = '12345678';
        $newPassword = '123456789';
        $request = $this->createFormRequest(
            'PUT', // Request to change password
            $this->urlFor('change-password-submit', ['user_id' => $userToUpdateRow['id']]),
            [
                'old_password' => $oldPassword,
                'password' => $newPassword,
                'password2' => $newPassword,
            ]
        );

        $response = $this->app->handle($request);

        // Assert status code
        self::assertSame($expectedResult[StatusCodeInterface::class], $response->getStatusCode());

        // Assert that password was changed or not changed
        $dbPasswordHash = $this->getTableRowById('user', $userToUpdateRow['id'])['password_hash'];
        if ($expectedResult['db_changed'] === true) {
            // Assert that password_hash starts with the beginning of a BCRYPT hash
            // Hash algo may change in the future, but it's not a big deal to update if tests fail
            self::assertStringStartsWith(
                '$2y$10$',
                $dbPasswordHash,
                'password_hash not starting with beginning of bcrypt hash'
            );
            // Verify that hash matches the given password
            self::assertTrue(password_verify($newPassword, $dbPasswordHash));
        }else{
            // Verify that hash matches the old password
            self::assertTrue(password_verify($oldPassword, $dbPasswordHash));
        }

        $this->assertJsonData($expectedResult['json_response'], $response);
    }

    /**
     * Test that backend validation fails when new passwords are invalid
     */
    public function testChangePassword_invalidData(): void
    {
        $oldPassword = '12345678'; // See fixture
        // Insert user id 2 role: user
        $userRow = (new UserFixture())->records[1];
        $this->insertFixture('user', $userRow);
        $userId = $userRow['id'];

        // Simulate logged-in user with id 2
        $this->container->get(SessionInterface::class)->set('user_id', $userId);

        $request = $this->createFormRequest(
            'POST', // Request to change password
            $this->urlFor('change-password-submit'),
            [
                'old_password' => $oldPassword,
                'password' => '1', // too short
                'password2' => '12', // too short and not similar
            ]
        );

        $response = $this->app->handle($request);

        // Assert that response has error status 422
        self::assertSame(StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY, $response->getStatusCode());

        // As form is directly rendered with validation errors it's not possible to test them as response is a stream
        // There is a visual test in insomnia for this, but I couldn't manage to keep the login session
    }

    /**
     * When user is not logged in, the code goes to the Action class which returns $response with code 401
     * but then goes through UserAuthMiddleware.php which redirects to the login page (code 302).
     */
    public function testChangePassword_notLoggedIn(): void
    {
        // NOT simulate login and not necessary to insert fixture

        $request = $this->createFormRequest(
            'POST', // Request to change password
            $this->urlFor('change-password-submit'),
            [
                'old_password' => '12345678',
                'password' => '123456789',
                'password2' => '123456789',
            ]
        );

        $response = $this->app->handle($request);

        // Assert: 302 Found meaning redirect (to login page)
        self::assertSame(StatusCodeInterface::STATUS_FOUND, $response->getStatusCode());

        // Test that redirect link after login is correct
        $expectedLoginUrl = $this->urlFor('login-page', [], ['redirect' => $this->urlFor('change-password-page')]);
        $actualUrl = $response->getHeaderLine('Location');
        self::assertSame($expectedLoginUrl, $actualUrl);
    }


    /**
     * Empty or malformed request body is when parameters are not set or have
     * the wrong name ("key").
     *
     * If the request contains a different body than expected, HttpBadRequestException
     * is thrown and an error page is displayed to the user because that means that
     * there is an error with the client sending the request that has to be fixed.
     *
     * @dataProvider \App\Test\Provider\User\UserDataProvider::malformedPasswordChangeRequestBodyProvider()
     *
     * @param array|null $malformedBody null for the case that request body is null
     * @param string $message
     */
    public function testChangePassword_malformedBody(null|array $malformedBody, string $message): void
    {
        $oldPassword = '12345678'; // See fixture
        // Insert user id 2 role: user
        $userRow = (new UserFixture())->records[1];
        $this->insertFixture('user', $userRow);
        $userId = $userRow['id'];

        // Simulate logged-in user with id 2
        $this->container->get(SessionInterface::class)->set('user_id', $userId);

        $malformedRequest = $this->createFormRequest(
            'POST', // Request to change password
            $this->urlFor('change-password-submit'),
            $malformedBody
        );

        // Bad Request (400) means that the client sent the request wrongly; it's a client error
        $this->expectException(HttpBadRequestException::class);
        $this->expectExceptionMessage($message);

        // Handle request after defining expected exceptions
        $this->app->handle($malformedRequest);
    }
}
