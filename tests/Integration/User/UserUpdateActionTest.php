<?php

namespace App\Test\Integration\User;

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
use Slim\Exception\HttpBadRequestException;

/**
 * Integration testing user update Process.
 */
class UserUpdateActionTest extends TestCase
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
     * User update process with valid data.
     *
     * @dataProvider \App\Test\Provider\User\UserUpdateProvider::userUpdateAuthorizationCases()
     *
     * @param array $userToChangeRow user to change attributes containing the user_role_id
     * @param array $authenticatedUserRow authenticated user attributes containing the user_role_id
     * @param array $requestData array of data for the request body
     * @param array $expectedResult HTTP status code, bool if db_entry_created and json_response
     *
     * @throws \JsonException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     *
     * @return void
     */
    public function testUserSubmitUpdateAuthorization(
        array $userToChangeRow,
        array $authenticatedUserRow,
        array $requestData,
        array $expectedResult
    ): void {
        // Insert authenticated user and user linked to resource with given attributes containing the user role
        $this->insertUserFixturesWithAttributes($userToChangeRow, $authenticatedUserRow);

        // Add user role id to $requestData as it could contain a user role enum
        $requestData = $this->addUserRoleId($requestData);

        $request = $this->createJsonRequest(
            'PUT',
            $this->urlFor('user-update-submit', ['user_id' => $userToChangeRow['id']]),
            $requestData
        );
        // Simulate logged-in user with logged-in user id
        $this->container->get(SessionInterface::class)->set('user_id', $authenticatedUserRow['id']);
        $response = $this->app->handle($request);
        // Assert status code
        self::assertSame($expectedResult[StatusCodeInterface::class], $response->getStatusCode());
        // Assert database
        if ($expectedResult['db_changed'] === true) {
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
        $this->assertJsonData($expectedResult['json_response'], $response);
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
        $request = $this->createJsonRequest('PUT', $this->urlFor('user-update-submit', ['user_id' => 1]));
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
     * Test user submit invalid update data.
     *
     * @dataProvider \App\Test\Provider\User\UserUpdateProvider::invalidUserUpdateCases()
     *
     * @param array $requestBody
     * @param array $jsonResponse
     */
    public function testUserSubmitUpdateInvalid(array $requestBody, array $jsonResponse): void
    {
        // Insert user that is allowed to change content (advisor owner)
        $userRow = $this->insertFixturesWithAttributes(
            $this->addUserRoleId(['user_role_id' => UserRole::ADVISOR]),
            UserFixture::class
        );

        $request = $this->createJsonRequest(
            'PUT',
            $this->urlFor('user-update-submit', ['user_id' => $userRow['id']]),
            $requestBody
        );
        // Simulate logged-in user with logged-in user id
        $this->container->get(SessionInterface::class)->set('user_id', $userRow['id']);
        $response = $this->app->handle($request);
        // Assert 200 OK
        self::assertSame(StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        // database must be unchanged
        $this->assertTableRowEquals($userRow, 'user', $userRow['id']);
        $this->assertJsonData($jsonResponse, $response);
    }

    /**
     * Empty or malformed request body is when parameters
     * are not set or have the wrong name ("key").
     * Example: Server needs the argument "email" to process
     * the request but "email" is not present in the body or
     * misspelled.
     * Good: "email: valid_or_invalid@data.com"
     * Bad: "emal: valid_or_invalid@data.com".
     *
     * If the request contains a different body than expected, HttpBadRequestException
     * is thrown and an error page is displayed to the user because that means that
     * there is an error with the client sending the request that has to be fixed.
     */
    public function testUserSubmitUpdateMalformedBody(): void
    {
        // Action class should directly return error so only logged-in user has to be inserted
        $userRow = $this->insertFixturesWithAttributes([], UserFixture::class);
        // Simulate logged-in user with same user as linked to client
        $this->container->get(SessionInterface::class)->set('user_id', $userRow['id']);
        $request = $this->createJsonRequest(
            'PUT',
            $this->urlFor('user-update-submit', ['user_id' => $userRow['id']]),
            // The update request can format the request body pretty freely as long as it doesn't contain a non-allowed key
            // so to test only one invalid key is enough
            ['non_existing_key' => 'value']
        );
        // Bad Request (400) means that the client sent the request wrongly; it's a client error
        $this->expectException(HttpBadRequestException::class);
        $this->expectExceptionMessage('Request body malformed.');
        // Handle request after defining expected exceptions
        $this->app->handle($request);
    }
}
