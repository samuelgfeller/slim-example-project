<?php

namespace App\Test\Integration\User;

use App\Domain\Authentication\Repository\UserRoleFinderRepository;
use App\Domain\User\Enum\UserActivity;
use App\Domain\User\Enum\UserRole;
use App\Test\Fixture\UserFixture;
use App\Test\Traits\AppTestTrait;
use App\Test\Traits\AuthorizationTestTrait;
use App\Test\Traits\DatabaseExtensionTestTrait;
use App\Test\Traits\FixtureTestTrait;
use Fig\Http\Message\StatusCodeInterface;
use Odan\Session\SessionInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Selective\TestTrait\Traits\DatabaseTestTrait;
use Selective\TestTrait\Traits\HttpJsonTestTrait;
use Selective\TestTrait\Traits\HttpTestTrait;
use Selective\TestTrait\Traits\RouteTestTrait;

/**
 * Integration testing user creation.
 */
class UserCreateActionTest extends TestCase
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
     * Create user authorization test with different user roles.
     *
     * @dataProvider \App\Test\Provider\User\UserCreateProvider::userCreateAuthorizationCases()
     *
     * @param array $authenticatedUserAttr authenticated user attributes containing the user_role_id
     * @param UserRole|null $newUserRole user role id of new user
     * @param array $expectedResult HTTP status code, bool if db entry is created and json_response
     *
     * @return void
     */
    public function testUserSubmitCreateAuthorization(
        array $authenticatedUserAttr,
        ?UserRole $newUserRole,
        array $expectedResult
    ): void {
        $userRoleFinderRepository = $this->container->get(UserRoleFinderRepository::class);
        // Insert authenticated user and user linked to resource with given attributes containing the user role
        $authenticatedUserRow = $this->insertFixturesWithAttributes(
            $this->addUserRoleId($authenticatedUserAttr),
            new UserFixture()
        );

        $requestData = [
            'first_name' => 'Danny',
            'surname' => 'Ric',
            'email' => 'daniel.riccardo@notmclaren.com',
            'password' => '12345678',
            'password2' => '12345678',
            'user_role_id' => $newUserRole ? $userRoleFinderRepository->findUserRoleIdByName(
                $newUserRole->value
            ) : $newUserRole,
            'status' => 'unverified',
            'language' => 'en_US',
        ];

        $request = $this->createJsonRequest(
            'POST',
            $this->urlFor('user-create-submit'),
            $requestData
        );
        // Simulate logged-in user with logged-in user id
        $this->container->get(SessionInterface::class)->set('user_id', $authenticatedUserRow['id']);
        $response = $this->app->handle($request);
        // Assert status code
        self::assertSame($expectedResult[StatusCodeInterface::class], $response->getStatusCode());
        // Assert database
        if ($expectedResult['db_changed'] === true) {
            $userDbRow = $this->findLastInsertedTableRow('user');
            // Request data can be taken to assert database as keys correspond to database columns after removing passwords
            unset($requestData['password'], $requestData['password2']);
            $this->assertTableRowEquals($requestData, 'user', $userDbRow['id']);

            // Assert that user activity is inserted
            $this->assertTableRow(
                [
                    'action' => UserActivity::CREATED->value,
                    'table' => 'user',
                    'row_id' => $userDbRow['id'],
                    'data' => json_encode($requestData, JSON_THROW_ON_ERROR),
                ],
                'user_activity',
                (int)$this->findTableRowsByColumn('user_activity', 'table', 'user')[0]['id']
            );

            // Assert that user activity is inserted
            $this->assertTableRow(
                [
                    'action' => UserActivity::CREATED->value,
                    'table' => 'user_verification',
                    'row_id' => (int)$this->findLastInsertedTableRow('user_verification')['id'],
                    // Data not asserted
                ],
                'user_activity',
                (int)$this->findTableRowsByColumn('user_activity', 'table', 'user_verification')[0]['id']
            );
        } else {
            // Only 1 rows (authenticated user) expected in user table
            $this->assertTableRowCount(1, 'user');
            $this->assertTableRowCount(0, 'user_activity');
        }
        $this->assertJsonData($expectedResult['json_response'], $response);
    }

    /**
     * Test that user is redirected to login page
     * if trying to do unauthenticated request.
     *
     * @return void
     */
    public function testUserSubmitCreateUnauthenticated(): void
    {
        // Request body doesn't have to be passed as missing session is caught in a middleware before the action
        $request = $this->createJsonRequest('POST', $this->urlFor('user-create-submit'));
        // Create url where user should be redirected to after login
        $redirectToUrlAfterLogin = $this->urlFor('user-list-page');
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
     * Test user submit invalid update data.
     *
     * @dataProvider \App\Test\Provider\User\UserCreateProvider::invalidUserCreateCases()
     *
     * @param array $requestBody
     * @param array $jsonResponse
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface
     */
    public function testUserSubmitCreateInvalid(array $requestBody, array $jsonResponse): void
    {
        // Insert user that is allowed to create user without any authorization limitation (admin)
        // Even if user_role_id is empty string or null
        $userRow = $this->insertFixturesWithAttributes(
            $this->addUserRoleId(['user_role_id' => UserRole::ADMIN]),
            new UserFixture()
        );

        $request = $this->createJsonRequest(
            'POST',
            $this->urlFor('user-create-submit'),
            $requestBody
        );
        // Simulate logged-in user with logged-in user id
        $this->container->get(SessionInterface::class)->set('user_id', $userRow['id']);
        $response = $this->app->handle($request);
        // Assert 422 Unprocessable Entity, which means validation error if request body contains user_role_id
        // even if it's an empty string
        self::assertSame(StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY, $response->getStatusCode());


        // Database must be unchanged - only one row (authenticated user) expected in user table
        $this->assertTableRowCount(1, 'user');
        $this->assertJsonData($jsonResponse, $response);
    }

    /**
     * Test that user with the same email as existing user cannot be created.
     *
     * @return void
     */
    public function testUserSubmitCreateEmailAlreadyExists(): void
    {
        // Insert authenticated admin
        $adminRow = $this->insertFixturesWithAttributes(
            $this->addUserRoleId(['user_role_id' => UserRole::ADMIN]),
            new UserFixture()
        );
        // Simulate logged-in user with logged-in user id
        $this->container->get(SessionInterface::class)->set('user_id', $adminRow['id']);

        $existingEmail = 'email@address.com';
        // Insert user with email that will be used in request to create a new one
        $this->insertFixturesWithAttributes(['email' => $existingEmail], new UserFixture());

        $request = $this->createJsonRequest(
            'POST',
            $this->urlFor('user-create-submit'),
            [
                'first_name' => 'New User',
                'surname' => 'Same Email',
                'email' => $existingEmail,
                'password' => '12345678',
                'password2' => '12345678',
                'user_role_id' => 1,
                'status' => 'unverified',
                'language' => 'en_US',
            ]
        );

        $response = $this->app->handle($request);

        // Assert 422 Unprocessable Entity Validation error
        self::assertSame(StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY, $response->getStatusCode());

        // Database must be unchanged - only 2 rows (authenticated user and other inserted user)
        $this->assertTableRowCount(2, 'user');
        $this->assertJsonData([
            'status' => 'error',
            'message' => 'Validation error',
            'data' => [
                'errors' => ['email' => ['Email already exists']],
            ],
        ], $response);
    }
}
