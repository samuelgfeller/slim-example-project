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
 *  - change password authenticated - authorization
 *  - change password authenticated - invalid data -> 400 Bad request
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
     * @dataProvider \App\Test\Provider\User\UserChangePasswordCaseProvider::userPasswordChangeAuthorizationCases()
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
            // Assert that password_hash starts with the beginning of a BCRYPT hash.
            // Hash algo may change in the future, but it's not a big deal to update if tests fail
            self::assertStringStartsWith(
                '$2y$10$',
                $dbPasswordHash,
                'password_hash not starting with beginning of bcrypt hash'
            );
            // Verify that hash matches the given password
            self::assertTrue(password_verify($newPassword, $dbPasswordHash));
        } else {
            // Verify that hash matches the old password
            self::assertTrue(password_verify($oldPassword, $dbPasswordHash));
        }

        $this->assertJsonData($expectedResult['json_response'], $response);
    }

    /**
     * Test that user is redirected to login page if trying to access page unauthenticated
     *
     * @return void
     */
    public function testChangePasswordSubmitAction_unauthenticated(): void
    {
        // Request body doesn't have to be passed as missing session is caught in a middleware before the action
        $request = $this->createJsonRequest('PUT', $this->urlFor('change-password-submit', ['user_id' => 1]));
        // Create url where user should be redirected to after login
        $redirectToUrlAfterLogin = $this->urlFor('user-read-page', ['user_id' => 1]);
        $request = $request->withAddedHeader('Redirect-to-url-if-unauthorized', $redirectToUrlAfterLogin);
        // Make request
        $response = $this->app->handle($request);
        // Assert response HTTP status code: 401 Unauthorized
        self::assertSame(StatusCodeInterface::STATUS_UNAUTHORIZED, $response->getStatusCode());
        // Build expected login url as UserAuthenticationMiddleware.php does
        $expectedLoginUrl = $this->urlFor('login-page', [], ['redirect' => $redirectToUrlAfterLogin]);
        // Assert that response contains correct login url
        $this->assertJsonData(['loginUrl' => $expectedLoginUrl], $response);
    }

    /**
     * Test that backend validation fails when new passwords are invalid
     *
     * @dataProvider \App\Test\Provider\User\UserChangePasswordCaseProvider::invalidPasswordChangeCases()
     * @param array $requestBody
     * @param array $jsonResponse
     * @return void
     */
    public function testChangePasswordSubmitAction_invalid(array $requestBody, array $jsonResponse): void
    {
        // Insert user that is allowed to change content
        $userRow = $this->insertFixturesWithAttributes(['user_role_id' => 3], UserFixture::class);

        $request = $this->createJsonRequest(
            'PUT',
            $this->urlFor('change-password-submit', ['user_id' => $userRow['id']]),
            $requestBody
        );
        // Simulate logged-in user with logged-in user id
        $this->container->get(SessionInterface::class)->set('user_id', $userRow['id']);
        $response = $this->app->handle($request);
        // Assert 200 OK
        self::assertSame(StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        // database should be unchanged
        $this->assertTableRowEquals($userRow, 'user', $userRow['id']);
        $this->assertJsonData($jsonResponse, $response);
    }

    /**
     * Empty or malformed request body is when parameters are not set or have
     * the wrong name ('key').
     *
     * @dataProvider \App\Test\Provider\User\UserChangePasswordCaseProvider::malformedPasswordChangeRequestCases()
     *
     * @param array|null $malformedRequestBody null for the case that request body is null
     */
    public function testChangePasswordSubmitAction_malformedBody(null|array $malformedRequestBody): void
    {
        // Action class should directly return error so only logged-in user has to be inserted
        $userRow = $this->insertFixturesWithAttributes([], UserFixture::class);
        // Simulate logged-in user with same user as linked to client
        $this->container->get(SessionInterface::class)->set('user_id', $userRow['id']);
        $request = $this->createJsonRequest(
            'PUT',
            $this->urlFor('change-password-submit', ['user_id' => $userRow['id']]),
            $malformedRequestBody
        );
        // Bad Request (400) means that the client sent the request wrongly; it's a client error
        $this->expectException(HttpBadRequestException::class);
        $this->expectExceptionMessage('Request body malformed.');
        // Handle request after defining expected exceptions
        $this->app->handle($request);
    }
}
