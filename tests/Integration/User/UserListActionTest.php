<?php

namespace App\Test\Integration\User;

use App\Domain\User\Enum\UserStatus;
use App\Test\Traits\AppTestTrait;
use App\Test\Traits\AuthorizationTestTrait;
use App\Test\Traits\FixtureTestTrait;
use Fig\Http\Message\StatusCodeInterface;
use Odan\Session\SessionInterface;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Selective\TestTrait\Traits\DatabaseTestTrait;
use Selective\TestTrait\Traits\HttpJsonTestTrait;
use Selective\TestTrait\Traits\HttpTestTrait;
use Selective\TestTrait\Traits\RouteTestTrait;

class UserListActionTest extends TestCase
{
    use AppTestTrait;
    use HttpTestTrait;
    use HttpJsonTestTrait;
    use RouteTestTrait;
    use DatabaseTestTrait;
    use FixtureTestTrait;
    use AuthorizationTestTrait;

    /**
     * Tests users that are loaded with ajax on user list page.
     * One authenticated user and only one other is tested at a time for clarity and simplicity.
     *
     * @param array $userRow user attributes containing the user_role_id
     * @param array $authenticatedUserRow authenticated user attributes containing the user_role_id
     * @param array $expectedResult HTTP status code and privilege
     *
     *@throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     *
     * @return void
     */
    #[DataProviderExternal(\App\Test\Provider\User\UserListProvider::class, 'userListAuthorizationCases')]
    public function testUserListAuthorization(
        array $userRow,
        array $authenticatedUserRow,
        array $expectedResult,
    ): void {
        // Change user attributes to user data
        $this->insertUserFixturesWithAttributes($authenticatedUserRow, $userRow);

        // Simulate logged-in user by setting the user_id session variable
        $this->container->get(SessionInterface::class)->set('user_id', $authenticatedUserRow['id']);
        // Make request
        $request = $this->createJsonRequest('GET', $this->urlFor('user-list'));
        $response = $this->app->handle($request);
        // Assert status code
        self::assertSame($expectedResult[StatusCodeInterface::class], $response->getStatusCode());

        // Init expected response array general format
        $expectedResponseArray = [
            'userResultDataArray' => [],
            'statuses' => UserStatus::toTranslatedNamesArray(),
        ];

        // Add response array of authenticated user to the expected userResultDataArray
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
            'statusPrivilege' => $expectedResult['own']['statusPrivilege']->name,
            'userRolePrivilege' => $expectedResult['own']['userRolePrivilege']->name,
            'availableUserRoles' => $this->formatAvailableUserRoles($expectedResult['own']['availableUserRoles']),
        ];

        // Add response array of the other user if it is set
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
                'statusPrivilege' => $expectedResult['other']['statusPrivilege']->name,
                'userRolePrivilege' => $expectedResult['other']['userRolePrivilege']->name,
                'availableUserRoles' => $this->formatAvailableUserRoles($expectedResult['other']['availableUserRoles']),
            ];
        }

        // Assert response data
        $this->assertJsonData($expectedResponseArray, $response);
    }

    /**
     * Change array of UserRole Enum cases to expected availableUserRoles
     * array from the server with id and capitalized role name [{id} => {Role name}].
     *
     * @param array $userRoles user roles with Enum cases array
     *
     * @return array
     */
    protected function formatAvailableUserRoles(array $userRoles): array
    {
        $formattedRoles = [];
        foreach ($userRoles as $userRole) {
            // Key is role id and value the name for the dropdown
            $formattedRoles[$this->getUserRoleIdByEnum($userRole)] = $userRole->roleNameForDropdown();
        }

        return $formattedRoles;
    }

    /**
     * Test user list action when not logged-in.
     *
     * @return void
     */
    public function testUserListUnauthenticated(): void
    {
        $request = $this->createJsonRequest('GET', $this->urlFor('user-list'));

        // Make request
        $response = $this->app->handle($request);

        // Assert response HTTP status code: 401 Unauthorized
        self::assertSame(StatusCodeInterface::STATUS_UNAUTHORIZED, $response->getStatusCode());

        // Assert that response contains correct login url
        $this->assertJsonData(['loginUrl' => $this->urlFor('login-page')], $response);
    }
}
