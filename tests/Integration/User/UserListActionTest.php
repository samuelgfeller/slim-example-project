<?php

namespace App\Test\Integration\User;

use App\Domain\Authorization\Privilege;
use App\Domain\User\Enum\UserRole;
use App\Domain\User\Enum\UserStatus;
use App\Test\Traits\AppTestTrait;
use App\Test\Traits\AuthorizationTestTrait;
use App\Test\Traits\FixtureTestTrait;
use Fig\Http\Message\StatusCodeInterface;
use Odan\Session\SessionInterface;
use PHPUnit\Framework\TestCase;
use Selective\TestTrait\Traits\DatabaseTestTrait;
use Selective\TestTrait\Traits\HttpJsonTestTrait;
use Selective\TestTrait\Traits\RouteTestTrait;

class UserListActionTest extends TestCase
{
    use AppTestTrait;
    use HttpJsonTestTrait;
    use RouteTestTrait;
    use DatabaseTestTrait;
    use FixtureTestTrait;
    use AuthorizationTestTrait;

    /**
     * Tests users that are loaded with ajax on user list page.
     * One authenticated user and only one other is tested at a time for clarity and simplicity.
     *
     * @dataProvider \App\Test\Provider\User\UserListProvider::userListAuthorizationCases()
     *
     * @param array $userRow user attributes containing the user_role_id
     * @param array $authenticatedUserRow authenticated user attributes containing the user_role_id
     * @param array{
     *        string: StatusCodeInterface,
     *        own: array{
     *          statusPrivilege: Privilege,
     *          userRolePrivilege: Privilege,
     *          availableUserRoles: UserRole        },
     *        other: null|array{
     *          statusPrivilege: Privilege,
     *          userRolePrivilege: Privilege,
     *          availableUserRoles: UserRole        }
     *     }
     * $expectedResult HTTP status code and privilege
     *
     * @return void
     */
    public function testUserListAuthorization(
        array $userRow,
        array $authenticatedUserRow,
        array $expectedResult,
    ): void {
        // Change user attributes to user data
        $this->insertUserFixturesWithAttributes($userRow, $authenticatedUserRow);

        // Simulate logged-in user with logged-in user id
        $this->container->get(SessionInterface::class)->set('user_id', $authenticatedUserRow['id']);
        // Make request
        $request = $this->createJsonRequest('GET', $this->urlFor('user-list'));
        $response = $this->app->handle($request);
        // Assert status code
        self::assertSame($expectedResult[StatusCodeInterface::class], $response->getStatusCode());

        // Init expected response array general format
        $expectedResponseArray = [
            'userResultDataArray' => [],
            'statuses' => UserStatus::toArray(),
        ];

        // Add response array of authenticated user to expected userResultDataArray
        $expectedResponseArray['userResultDataArray'][] = [
            // camelCase according to Google recommendation https://stackoverflow.com/a/19287394/9013718
            'id' => $authenticatedUserRow['id'],
            'firstName' => $authenticatedUserRow['first_name'],
            'surname' => $authenticatedUserRow['surname'],
            'email' => $authenticatedUserRow['email'],
            'status' => $authenticatedUserRow['status'],
            'userRoleId' => $authenticatedUserRow['user_role_id'],
            'updatedAt' => $authenticatedUserRow['updated_at'],
            'createdAt' => $authenticatedUserRow['created_at'],
            'statusPrivilege' => $expectedResult['own']['statusPrivilege']->value,
            'userRolePrivilege' => $expectedResult['own']['userRolePrivilege']->value,
            'availableUserRoles' => $this->formatAvailableUserRoles($expectedResult['own']['availableUserRoles']),
        ];

        // Add response array of other user if it is set
        if ($expectedResult['other'] !== false) {
            $expectedResponseArray['userResultDataArray'][] = [
                'id' => $userRow['id'],
                'firstName' => $userRow['first_name'],
                'surname' => $userRow['surname'],
                'email' => $userRow['email'],
                'status' => $userRow['status'],
                'userRoleId' => $userRow['user_role_id'],
                'updatedAt' => $userRow['updated_at'],
                'createdAt' => $userRow['created_at'],
                'statusPrivilege' => $expectedResult['other']['statusPrivilege']->value,
                'userRolePrivilege' => $expectedResult['other']['userRolePrivilege']->value,
                'availableUserRoles' => $this->formatAvailableUserRoles($expectedResult['other']['availableUserRoles']),
            ];
        }

        // Assert response data
        $this->assertJsonData($expectedResponseArray, $response);
    }

    /**
     * Test user list action when not logged-in.
     *
     * @return void
     */
    public function testUserListUnauthenticated(): void
    {
        $request = $this->createJsonRequest('GET', $this->urlFor('user-list'));

        $redirectToUrlAfterLogin = $this->urlFor('user-list-page');
        $request = $request->withAddedHeader('Redirect-to-url-if-unauthorized', $redirectToUrlAfterLogin);

        // Make request
        $response = $this->app->handle($request);

        // Assert response HTTP status code: 401 Unauthorized
        self::assertSame(StatusCodeInterface::STATUS_UNAUTHORIZED, $response->getStatusCode());

        // Build expected login url as UserAuthenticationMiddleware.php does
        $expectedLoginUrl = $this->urlFor(
            'login-page',
            [],
            ['redirect' => $redirectToUrlAfterLogin]
        );
        // Assert that response contains correct login url
        $this->assertJsonData(['loginUrl' => $expectedLoginUrl], $response);
    }
}
