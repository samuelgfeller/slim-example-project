<?php

namespace App\Test\Integration\User;

use App\Domain\Authorization\Privilege;
use App\Domain\User\Enum\UserRole;
use App\Domain\User\Enum\UserStatus;
use App\Test\Traits\AppTestTrait;
use App\Test\Traits\AuthorizationTestTrait;
use App\Test\Traits\FixtureTestTrait;
use App\Test\Traits\RouteTestTrait;
use Fig\Http\Message\StatusCodeInterface;
use Odan\Session\SessionInterface;
use PHPUnit\Framework\TestCase;
use Selective\TestTrait\Traits\DatabaseTestTrait;
use Selective\TestTrait\Traits\HttpJsonTestTrait;

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
     * @dataProvider \App\Test\Provider\User\UserListCaseProvider::userListAuthorizationCases()
     *
     * @param array $userData user attributes containing the user_role_id
     * @param array $authenticatedUserData authenticated user attributes containing the user_role_id
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
     * @return void
     */
    public function testUserList_authorization(
        array $userData,
        array $authenticatedUserData,
        array $expectedResult,
    ): void {
        // Changing user attributes to user data
        $this->insertUserFixturesWithAttributes($userData, $authenticatedUserData);

        // Simulate logged-in user with logged-in user id
        $this->container->get(SessionInterface::class)->set('user_id', $authenticatedUserData['id']);
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
            'firstName' => $authenticatedUserData['first_name'],
            'surname' => $authenticatedUserData['surname'],
            'email' => $authenticatedUserData['email'],
            'id' => $authenticatedUserData['id'],
            'status' => $authenticatedUserData['status'],
            'updatedAt' => $authenticatedUserData['updated_at'],
            'createdAt' => $authenticatedUserData['created_at'],
            'userRoleId' => $authenticatedUserData['user_role_id'],
            'statusPrivilege' => $expectedResult['own']['statusPrivilege']->value,
            'userRolePrivilege' => $expectedResult['own']['userRolePrivilege']->value,
            'availableUserRoles' => $this->formatAvailableUserRoles($expectedResult['own']['availableUserRoles']),
        ];

        // Add response array of other user if it is set
        if ($expectedResult['other'] !== false) {
            $expectedResponseArray['userResultDataArray'][] = [
                'firstName' => $userData['first_name'],
                'surname' => $userData['surname'],
                'email' => $userData['email'],
                'id' => $userData['id'],
                'status' => $userData['status'],
                'updatedAt' => $userData['updated_at'],
                'createdAt' => $userData['created_at'],
                'userRoleId' => $userData['user_role_id'],
                'statusPrivilege' => $expectedResult['other']['statusPrivilege']->value,
                'userRolePrivilege' => $expectedResult['other']['userRolePrivilege']->value,
                'availableUserRoles' => $this->formatAvailableUserRoles($expectedResult['other']['availableUserRoles']),
            ];
        }

        // Assert response data
        $this->assertJsonData($expectedResponseArray, $response);
    }
}