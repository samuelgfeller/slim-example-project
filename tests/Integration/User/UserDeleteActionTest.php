<?php

namespace App\Test\Integration\User;

use App\Modules\User\Enum\UserActivity;
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
 * User delete action tests
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
    use FixtureTestTrait;
    use AuthorizationTestTrait;

    /**
     * Test delete user submit with different authenticated user roles.
     *
     * @param array $userToDeleteRow user to delete attributes containing user_role_id
     * @param array $authenticatedUserRow authenticated user attributes containing the user_role_id
     * @param array $expectedResult HTTP status code, bool if db is supposed to change and json_response
     *
     * @return void
     */
    #[DataProviderExternal(\App\Test\Provider\User\UserDeleteProvider::class, 'userDeleteAuthorizationCases')]
    public function testUserSubmitDeleteActionAuthorization(
        array $userToDeleteRow,
        array $authenticatedUserRow,
        array $expectedResult,
    ): void {
        // Insert authenticated user and user to delete with given attributes containing the user role
        $this->insertUserFixtures($authenticatedUserRow, $userToDeleteRow);

        // Simulate logged-in user
        $this->container->get(SessionInterface::class)->set('user_id', $authenticatedUserRow['id']);

        $request = $this->createJsonRequest(
            'DELETE',
            // Construct url /users/1 with urlFor()
            $this->urlFor('user-delete-submit', ['user_id' => $userToDeleteRow['id']]),
        );

        $response = $this->app->handle($request);

        // Assert status code
        self::assertSame($expectedResult[StatusCodeInterface::class], $response->getStatusCode());

        // Assert database
        if ($expectedResult['dbChanged'] === true) {
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
        $this->assertJsonData($expectedResult['jsonResponse'], $response);
    }

    /**
     * Test that when the user is not logged in 401 Unauthorized is returned.
     *
     * @return void
     */
    public function testUserSubmitDeleteActionUnauthenticated(): void
    {
        $request = $this->createJsonRequest(
            'DELETE',
            $this->urlFor('user-delete-submit', ['user_id' => '1']),
        );

        $response = $this->app->handle($request);

        // Assert response HTTP status code: 401 Unauthorized
        self::assertSame(StatusCodeInterface::STATUS_UNAUTHORIZED, $response->getStatusCode());

        // Assert that response contains correct login url
        $this->assertJsonData(['loginUrl' => $this->urlFor('login-page')], $response);
    }
}
