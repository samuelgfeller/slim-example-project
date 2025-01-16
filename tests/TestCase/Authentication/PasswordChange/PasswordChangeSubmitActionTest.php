<?php

namespace App\Test\TestCase\Authentication\PasswordChange;

use App\Module\User\Enum\UserActivity;
use App\Module\User\Enum\UserRole;
use App\Test\Fixture\UserFixture;
use App\Test\Trait\AppTestTrait;
use App\Test\Trait\AuthorizationTestTrait;
use Fig\Http\Message\StatusCodeInterface;
use Odan\Session\SessionInterface;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use TestTraits\Trait\DatabaseTestTrait;
use TestTraits\Trait\FixtureTestTrait;
use TestTraits\Trait\HttpJsonTestTrait;
use TestTraits\Trait\HttpTestTrait;
use TestTraits\Trait\RouteTestTrait;

/**
 * Integration testing password change from authenticated user
 *  - change password authenticated - authorization
 *  - change password authenticated - invalid data
 *  - change password not authenticated -> 302 to login page with correct redirect param.
 */
class PasswordChangeSubmitActionTest extends TestCase
{
    use AppTestTrait;
    use HttpTestTrait;
    use RouteTestTrait;
    use DatabaseTestTrait;
    use FixtureTestTrait;
    use HttpJsonTestTrait;
    use AuthorizationTestTrait;

    /**
     * Test user password change with different user roles.
     *
     * @param array $userToUpdateRow
     * @param array $authenticatedUserRow
     * @param array $expectedResult
     */
    #[DataProviderExternal(\App\Test\TestCase\Authentication\PasswordChange\UserChangePasswordProvider::class, 'userPasswordChangeAuthorizationCases')]
    public function testChangePasswordSubmitActionAuthorization(
        array $userToUpdateRow,
        array $authenticatedUserRow,
        array $expectedResult,
    ): void {
        // Insert authenticated user and user linked to resource with given attributes containing the user role
        $this->insertUserFixtures($authenticatedUserRow, $userToUpdateRow);

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
        if ($expectedResult['dbChanged'] === true) {
            // Assert that password_hash starts with the beginning of a BCRYPT hash.
            // Hash algo may change in the future, but it's not a big deal to update if tests fail
            self::assertStringStartsWith(
                '$2y$10$',
                $dbPasswordHash,
                'password_hash not starting with beginning of bcrypt hash'
            );
            // Verify that hash matches the given password
            self::assertTrue(password_verify($newPassword, $dbPasswordHash));

            // Assert that user activity is inserted
            $this->assertTableRow(
                [
                    'action' => UserActivity::UPDATED->value,
                    'table' => 'user',
                    'row_id' => $userToUpdateRow['id'],
                    'data' => json_encode(['password_hash' => '******'], JSON_THROW_ON_ERROR),
                ],
                'user_activity',
                (int)$this->findLastInsertedTableRow('user_activity')['id']
            );
        } else {
            // Verify that hash matches the old password
            self::assertTrue(password_verify($oldPassword, $dbPasswordHash));
            $this->assertTableRowCount(0, 'user_activity');
        }

        $this->assertJsonData($expectedResult['jsonResponse'], $response);
    }

    /**
     * Test that user is redirected to login page
     * if trying to do unauthenticated request.
     *
     * @return void
     */
    public function testChangePasswordSubmitActionUnauthenticated(): void
    {
        // Request body doesn't have to be passed as missing session is caught in a middleware before the action
        $request = $this->createJsonRequest('PUT', $this->urlFor('change-password-submit', ['user_id' => '1']));

        // Make request
        $response = $this->app->handle($request);
        // Assert response HTTP status code: 401 Unauthorized
        self::assertSame(StatusCodeInterface::STATUS_UNAUTHORIZED, $response->getStatusCode());
        // Build expected login url as UserAuthenticationMiddleware.php does
        $expectedLoginUrl = $this->urlFor('login-page');
        // Assert that response contains correct login url
        $this->assertJsonData(['loginUrl' => $expectedLoginUrl], $response);
    }

    /**
     * Test that backend validation fails when new passwords are invalid.
     *
     * @param array $requestBody
     * @param array $jsonResponse
     *
     * @return void
     */
    #[DataProviderExternal(\App\Test\TestCase\Authentication\PasswordChange\UserChangePasswordProvider::class, 'invalidPasswordChangeCases')]
    public function testChangePasswordSubmitActionInvalid(array $requestBody, array $jsonResponse): void
    {
        // Insert user that is allowed to change content
        $userRow = $this->insertFixture(
            UserFixture::class,
            $this->addUserRoleId(['user_role_id' => UserRole::ADVISOR]),
        );

        $request = $this->createJsonRequest(
            'PUT',
            $this->urlFor('change-password-submit', ['user_id' => $userRow['id']]),
            $requestBody
        );
        // Simulate logged-in user by setting the user_id session variable
        $this->container->get(SessionInterface::class)->set('user_id', $userRow['id']);
        $response = $this->app->handle($request);
        // Assert 422 Unprocessable Entity
        self::assertSame(StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        // database should be unchanged
        $this->assertTableRowEquals($userRow, 'user', $userRow['id']);
        $this->assertJsonData($jsonResponse, $response);
    }
}
