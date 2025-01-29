<?php

namespace App\Test\TestCase\User\Update;

use App\Module\User\Enum\UserActivity;
use App\Module\User\Enum\UserRole;
use App\Test\Fixture\UserFixture;
use App\Test\Trait\AppTestTrait;
use App\Test\Trait\AuthorizationTestTrait;
use Fig\Http\Message\StatusCodeInterface;
use Odan\Session\SessionInterface;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use TestTraits\Trait\DatabaseTestTrait;
use TestTraits\Trait\FixtureTestTrait;
use TestTraits\Trait\HttpJsonTestTrait;
use TestTraits\Trait\HttpTestTrait;
use TestTraits\Trait\RouteTestTrait;

/**
 * Integration testing user update.
 */
class UserUpdateActionTest extends TestCase
{
    use AppTestTrait;
    use HttpTestTrait;
    use HttpJsonTestTrait;
    use RouteTestTrait;
    use DatabaseTestTrait;
    use FixtureTestTrait;
    use AuthorizationTestTrait;

    /**
     * User update process with valid data.
     *
     * @param array $userToChangeRow user to change attributes containing the user_role_id
     * @param array $authenticatedUserRow authenticated user attributes containing the user_role_id
     * @param array $requestData array of data for the request body
     * @param array $expectedResult HTTP status code, bool if db entry is created and json response
     *
     * @throws \JsonException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     *
     * @return void
     */
    #[DataProviderExternal(UserUpdateProvider::class, 'userUpdateAuthorizationCases')]
    public function testUserSubmitUpdateAuthorization(
        array $userToChangeRow,
        array $authenticatedUserRow,
        array $requestData,
        array $expectedResult,
    ): void {
        // Insert authenticated user and user linked to resource with given attributes containing the user role
        $this->insertUserFixtures($authenticatedUserRow, $userToChangeRow);

        // Add user role id to $requestData as it could contain a user role enum
        $requestData = $this->addUserRoleId($requestData);

        $request = $this->createJsonRequest(
            'PUT',
            $this->urlFor('user-update-submit', ['user_id' => $userToChangeRow['id']]),
            $requestData
        );
        // Simulate logged-in user by setting the user_id session variable
        $this->container->get(SessionInterface::class)->set('user_id', $authenticatedUserRow['id']);
        $response = $this->app->handle($request);
        // Assert status code
        self::assertSame($expectedResult[StatusCodeInterface::class], $response->getStatusCode());
        // Assert database
        if ($expectedResult['dbChanged'] === true) {
            // HTML form element names are the same as the database columns, the same request array can be taken to assert the db
            // Check that data in request body was changed
            $this->assertTableRowEquals($requestData, 'user', $userToChangeRow['id']);

            // Assert that user activity is inserted
            $this->assertTableRow(
                [
                    'action' => UserActivity::UPDATED->value,
                    'table' => 'user',
                    'row_id' => $userToChangeRow['id'],
                    'data' => json_encode($requestData, JSON_THROW_ON_ERROR),
                ],
                'user_activity',
                (int)$this->findLastInsertedTableRow('user_activity')['id']
            );
        } else {
            // If db is not expected to change, data should remain the same as when it was inserted from the fixture
            $this->assertTableRowEquals($userToChangeRow, 'user', $userToChangeRow['id']);
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
    public function testUserSubmitUpdateUnauthenticated(): void
    {
        // Request body doesn't have to be passed as missing session is caught in a middleware before the action
        $request = $this->createJsonRequest('PUT', $this->urlFor('user-update-submit', ['user_id' => '1']));

        // Make request
        $response = $this->app->handle($request);
        // Assert response HTTP status code: 401 Unauthorized
        self::assertSame(StatusCodeInterface::STATUS_UNAUTHORIZED, $response->getStatusCode());

        // Assert that response contains correct login url
        $this->assertJsonData(['loginUrl' => $this->urlFor('login-page')], $response);
    }

    /**
     * Test user submit invalid update data.
     *
     * @param array $requestBody
     * @param array $jsonResponse
     */
    #[DataProviderExternal(UserUpdateProvider::class, 'invalidUserUpdateCases')]
    public function testUserSubmitUpdateInvalid(array $requestBody, array $jsonResponse): void
    {
        // Insert user that is allowed to change content (advisor owner)
        $userRow = $this->insertFixture(
            // Replace user_role_id enum case with database id with AuthorizationTestTrait function addUserRoleId()
            UserFixture::class,
            $this->addUserRoleId(['user_role_id' => UserRole::ADVISOR]),
        );

        $request = $this->createJsonRequest(
            'PUT',
            $this->urlFor('user-update-submit', ['user_id' => $userRow['id']]),
            $requestBody
        );
        // Simulate logged-in user by setting the user_id session variable
        $this->container->get(SessionInterface::class)->set('user_id', $userRow['id']);
        $response = $this->app->handle($request);
        // Assert 422 Unprocessable Entity
        self::assertSame(StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        // Database must be unchanged
        $this->assertTableRowEquals($userRow, 'user', $userRow['id']);
        $this->assertJsonData($jsonResponse, $response);
    }
}
