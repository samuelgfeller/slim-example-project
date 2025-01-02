<?php

namespace App\Test\Integration\User;

use App\Modules\Authentication\Repository\UserRoleFinderRepository;
use App\Modules\User\Enum\UserActivity;
use App\Modules\User\Enum\UserRole;
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
use TestTraits\Trait\MailerTestTrait;
use TestTraits\Trait\RouteTestTrait;

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
    use FixtureTestTrait;
    use AuthorizationTestTrait;
    use MailerTestTrait;

    /**
     * Create user authorization test with different user roles.
     *
     * @param array $authenticatedUserAttr authenticated user attributes containing the user_role_id
     * @param UserRole|null $newUserRole user role id of new user
     * @param array $expectedResult HTTP status code, bool if db entry is created and json_response
     *
     * @return void
     */
    #[DataProviderExternal(\App\Test\Provider\User\UserCreateProvider::class, 'userCreateAuthorizationCases')]
    public function testUserSubmitCreateAuthorization(
        array $authenticatedUserAttr,
        ?UserRole $newUserRole,
        array $expectedResult,
    ): void {
        $userRoleFinderRepository = $this->container->get(UserRoleFinderRepository::class);
        // Insert authenticated user and user linked to resource with given attributes containing the user role
        $authenticatedUserRow = $this->insertFixture(
            UserFixture::class,
            $this->addUserRoleId($authenticatedUserAttr),
        );

        $requestData = [
            'first_name' => 'Danny',
            'last_name' => 'Ric',
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

        // Simulate logged-in user by setting the user_id session variable
        $this->container->get(SessionInterface::class)->set('user_id', $authenticatedUserRow['id']);
        $response = $this->app->handle($request);
        // Assert status code
        self::assertSame($expectedResult[StatusCodeInterface::class], $response->getStatusCode());

        // Assert database
        if ($expectedResult['dbChanged'] === true) {
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
                    'data' => json_encode($requestData, JSON_PARTIAL_OUTPUT_ON_ERROR),
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

            // When the account is created and unverified, a verification link is sent to the user via the email
            // Assert that the correct email was sent (email body contains string)
            $email = $this->getMailerMessage();
            $this->assertEmailHtmlBodyContains(
                $email,
                'To verify that this email address belongs to you, please click on the following link',
            );
            // Assert that email was sent to the right person in the right format
            $this->assertEmailHeaderSame(
                $email,
                'To',
                $requestData['first_name'] . ' ' .
                $requestData['last_name'] . ' <' . $requestData['email'] . '>'
            );
        } else { // Database must be unchanged
            // Only 1 row (authenticated user) expected in user table
            $this->assertTableRowCount(1, 'user');
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
    public function testUserSubmitCreateUnauthenticated(): void
    {
        // Request body doesn't have to be passed as missing session is caught in a middleware before the action
        $request = $this->createJsonRequest('POST', $this->urlFor('user-create-submit'));

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
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface
     */
    #[DataProviderExternal(\App\Test\Provider\User\UserCreateProvider::class, 'invalidUserCreateCases')]
    public function testUserSubmitCreateInvalid(array $requestBody, array $jsonResponse): void
    {
        // Insert user that is allowed to create user without any authorization limitation (admin)
        // Even if user_role_id is empty string or null
        $userRow = $this->insertFixture(
            UserFixture::class,
            $this->addUserRoleId(['user_role_id' => UserRole::ADMIN]),
        );

        $request = $this->createJsonRequest(
            'POST',
            $this->urlFor('user-create-submit'),
            $requestBody
        );
        // Simulate logged-in user by setting the user_id session variable
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
        $adminRow = $this->insertFixture(
            UserFixture::class,
            $this->addUserRoleId(['user_role_id' => UserRole::ADMIN]),
        );
        // Simulate logged-in user by setting the user_id session variable
        $this->container->get(SessionInterface::class)->set('user_id', $adminRow['id']);

        $existingEmail = 'email@address.com';
        // Insert user with email that will be used in request to create a new one
        $this->insertFixture(UserFixture::class, ['email' => $existingEmail]);

        $request = $this->createJsonRequest(
            'POST',
            $this->urlFor('user-create-submit'),
            [
                'first_name' => 'New User',
                'last_name' => 'Same Email',
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
