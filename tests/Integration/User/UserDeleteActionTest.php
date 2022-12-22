<?php

namespace App\Test\Integration\User;

use App\Domain\User\Enum\UserActivity;
use App\Test\Traits\AppTestTrait;
use App\Test\Traits\AuthorizationTestTrait;
use App\Test\Traits\DatabaseExtensionTestTrait;
use App\Test\Traits\FixtureTestTrait;
use Fig\Http\Message\StatusCodeInterface;
use Odan\Session\SessionInterface;
use PHPUnit\Framework\TestCase;
use Selective\TestTrait\Traits\DatabaseTestTrait;
use Selective\TestTrait\Traits\HttpJsonTestTrait;
use Selective\TestTrait\Traits\HttpTestTrait;
use Selective\TestTrait\Traits\RouteTestTrait;

/**
 * User submit delete action tests
 *  - Authenticated with different user roles
 *  - Unauthenticated.
 */
class UserDeleteActionTest extends TestCase
{
    use AppTestTrait;
    use HttpTestTrait;
    use HttpJsonTestTrait;
    use RouteTestTrait;
    use DatabaseTestTrait;
    use DatabaseExtensionTestTrait;
    use FixtureTestTrait;
    use AuthorizationTestTrait;

    /**
     * Test delete user submit with different authenticated user roles.
     *
     * @dataProvider \App\Test\Provider\User\UserDeleteProvider::userDeleteAuthorizationCases()
     *
     * @param array $userToDeleteRow user to delete attributes containing user_role_id
     * @param array $authenticatedUserRow authenticated user attributes containing the user_role_id
     * @param array $expectedResult HTTP status code, bool if db is supposed to change and json_response
     *
     * @return void
     */
    public function testUserSubmitDeleteActionAuthorization(
        array $userToDeleteRow,
        array $authenticatedUserRow,
        array $expectedResult
    ): void {
        // Insert authenticated user and user to delete with given attributes containing the user role
        $this->insertUserFixturesWithAttributes($userToDeleteRow, $authenticatedUserRow);

        // Simulate logged-in user
        $this->container->get(SessionInterface::class)->set('user_id', $authenticatedUserRow['id']);

        $request = $this->createJsonRequest(
            'DELETE',
            // Post delete route with id like /posts/1
            $this->urlFor('user-delete-submit', ['user_id' => $userToDeleteRow['id']]),
        );

        $response = $this->app->handle($request);

        // Assert: 200 OK
        self::assertSame($expectedResult[StatusCodeInterface::class], $response->getStatusCode());

        // Assert database
        if ($expectedResult['db_changed'] === true) {
            // Assert that deleted_at is NOT null
            self::assertNotNull($this->getTableRowById('user', $userToDeleteRow['id'], ['deleted_at']));
            // Assert that user activity is inserted
            $this->assertTableRow(
                [
                    'action' => UserActivity::DELETED->value,
                    'table' => 'user',
                    'row_id' => $userToDeleteRow['id'],
                    'data' => null,
                ],
                'user_activity',
                (int)$this->findLastInsertedTableRow('user_activity')['id']
            );
        } else {
            // If db is not expected to change deleted at has to be null
            $this->assertTableRow(['deleted_at' => null], 'user', $userToDeleteRow['id']);
            $this->assertTableRowCount(0, 'user_activity');
        }

        // Assert response json content
        $this->assertJsonData($expectedResult['json_response'], $response);
    }

    /**
     * Test that when user is not logged in 401 Unauthorized is returned
     * and that the authentication middleware provides the correct login url
     * if Redirect-to-route-name-if-unauthorized header is set.
     *
     * @return void
     */
    public function testUserSubmitDeleteActionUnauthenticated(): void
    {
        $request = $this->createJsonRequest(
            'DELETE',
            $this->urlFor('user-delete-submit', ['user_id' => 1]),
        );

        // Provide redirect to if unauthorized header to test if UserAuthenticationMiddleware returns correct login url
        $redirectAfterLoginRouteName = 'user-list-page';
        $request = $request->withAddedHeader('Redirect-to-route-name-if-unauthorized', $redirectAfterLoginRouteName);

        $response = $this->app->handle($request);

        // Assert response HTTP status code: 401 Unauthorized
        self::assertSame(StatusCodeInterface::STATUS_UNAUTHORIZED, $response->getStatusCode());

        // Build expected login url as UserAuthenticationMiddleware.php does
        $expectedLoginUrl = $this->urlFor(
            'login-page',
            [],
            ['redirect' => $this->urlFor($redirectAfterLoginRouteName)]
        );

        // Assert that response contains correct login url
        $this->assertJsonData(['loginUrl' => $expectedLoginUrl], $response);
    }

    // Unchanged content test not done as it's not being used by the frontend
    // Malformed request body also not so relevant as there is no body for deletion
    // Invalid data not relevant either as there is no data in the request body
}
