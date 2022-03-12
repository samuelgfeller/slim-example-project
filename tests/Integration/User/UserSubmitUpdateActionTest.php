<?php

namespace App\Test\Integration\User;

use App\Test\Traits\AppTestTrait;
use App\Test\Fixture\UserFixture;
use Fig\Http\Message\StatusCodeInterface;
use Odan\Session\SessionInterface;
use PHPUnit\Framework\TestCase;
use Selective\TestTrait\Traits\DatabaseTestTrait;
use Selective\TestTrait\Traits\HttpJsonTestTrait;
use Selective\TestTrait\Traits\HttpTestTrait;
use App\Test\Traits\RouteTestTrait;
use Slim\Exception\HttpBadRequestException;

/**
 * Integration testing user update Process
 */
class UserSubmitUpdateActionTest extends TestCase
{

    use AppTestTrait;
    use HttpTestTrait;
    use HttpJsonTestTrait;
    use RouteTestTrait;
    use DatabaseTestTrait;

    /**
     * User update process with valid data
     *
     * @return void
     */
    public function testUpdateUser(): void
    {
        $this->insertFixtures([UserFixture::class]);

        // Simulate logged in user with id 1
        $this->container->get(SessionInterface::class)->set('user_id', 1);

        $request = $this->createJsonRequest('PUT', // Request to change user with id 1 (url: PUT /users/1)
            $this->urlFor('user-update-submit', ['user_id' => 1]), [
                'first_name' => 'Admina',
                'surname' => 'Example edited',
                'email' => 'edited_admin@example.com'
            ]);

        $response = $this->app->handle($request);

        // Assert: 200 OK
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        $expected = [
            // id is string as CakePHP Database returns always strings: https://stackoverflow.com/a/11913488/9013718
            'id' => '1',
            'first_name' => 'Admina',
            'surname' => 'Example edited',
            'email' => 'edited_admin@example.com',
            // Not password since the hash value always changes, it's asserted later
        ];

        // Assert that content of selected fields (which are the keys of the $expected array) are same as expected
        $this->assertTableRow($expected, 'user', 1, array_keys($expected));

        // Assert that field "id" of row with id 1 equals to "1" (CakePHP returns always strings)
        $this->assertTableRowValue('1', 'user', 1, 'id');
    }

    /**
     * Test behaviour when trying to exist a user that doesn't exist
     * and data fails makes validation fail.
     */
    public function testUpdateUser_invalidData(): void
    {
        $this->insertFixtures([UserFixture::class]);

        // Simulate logged in user with id 1
        $this->container->get(SessionInterface::class)->set('user_id', 1);

        $request = $this->createJsonRequest('PUT', // Request to change non-existent user with id 999
            $this->urlFor('user-update-submit', ['user_id' => 999]), // Invalid data
            [
                'first_name' => 'A',
                'surname' => 'E',
                'email' => '$edited_admin@exampl$e.com'
            ]);

        $response = $this->app->handle($request);

        // Assert HTTP status code
        self::assertSame(StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY, $response->getStatusCode());

        $this->assertJsonContentType($response);

        // Response standard: SLE-134
        $this->assertJsonData(
            [
                'status' => 'error',
                'message' => 'Validation error',
                'data' => [
                    'message' => 'User not found',
                    'errors' => [
                        0 => [
                            'field' => 'user',
                            'message' => 'User not existing',
                        ],
                        1 => [
                            'field' => 'first_name',
                            'message' => 'Required minimum length is 2',
                        ],
                        2 => [
                            'field' => 'surname',
                            'message' => 'Required minimum length is 2',
                        ],
                        3 => [
                            'field' => 'email',
                            'message' => 'Invalid email address',
                        ],
                    ],
                ],
            ],
            $response
        );
    }

    /**
     * Test that logged in but unauthorized user cannot change another user
     */
    public function testUpdateUser_forbidden(): void
    {
        $this->insertFixtures([UserFixture::class]);

        // Simulate logged in user with id 2 which has NOT admin rights
        $this->container->get(SessionInterface::class)->set('user_id', 2);

        $request = $this->createJsonRequest('PUT', // Request to change user with id 1 (which must not be allowed to)
            $this->urlFor('user-update-submit', ['user_id' => 1]), // Invalid data
            [
                // Same keys than HTML form
                'first_name' => 'Admina',
                'surname' => 'Example edited',
                'email' => 'edited_admin@example.com'
            ]);

        $response = $this->app->handle($request);

        // Status 401 when not authenticated and 403 (Forbidden) when not allowed (logged in but missing right)
        self::assertSame(StatusCodeInterface::STATUS_FORBIDDEN, $response->getStatusCode());

        // Admin user should be unchanged (same as in fixture)
        $expected = [
            'id' => '1',
            'first_name' => 'Admin',
            'surname' => 'Example',
            'email' => 'admin@example.com',
        ];

        // Assert that content of selected fields (which are the keys of the $expected array) are same as expected
        $this->assertTableRow($expected, 'user', 1, array_keys($expected));
    }

    /**
     * When user is not logged in, the code goes to the Action class which returns $response with code 401
     * but then goes through UserAuthMiddleware.php which redirects to the login page (code 302).
     */
    public function testUpdateUser_notLoggedIn(): void
    {
        $this->insertFixtures([UserFixture::class]);

        // Make user not logged in
        $this->container->get(SessionInterface::class)->clear();

        $request = $this->createJsonRequest('PUT', // Request to change user with id 1 (which must not be allowed to)
            $this->urlFor('user-update-submit', ['user_id' => 1]), // Same keys than HTML form
            [
                'first_name' => 'Admina',
                'surname' => 'Example edit',
                'email' => 'edited_admin@example.com'
            ]);
        // Provide redirect to if unauthorized header to test if UserAuthenticationMiddleware returns correct login url
        $redirectAfterLoginRouteName = 'profile-page';
        $request = $request->withAddedHeader('Redirect-to-if-unauthorized', $redirectAfterLoginRouteName);

        $response = $this->app->handle($request);
        // Server returns 401 on json requests when not logged in
        self::assertSame(StatusCodeInterface::STATUS_UNAUTHORIZED, $response->getStatusCode());

        // Expected body content
        // Assert that body contains the url to the login page with correct redirect get param back to own-posts
        $expectedBody = [
            'loginUrl' => $this->urlFor('login-page', [], ['redirect' => $this->urlFor($redirectAfterLoginRouteName)])
        ];
        $this->assertJsonData($expectedBody, $response);

        // Admin user should be unchanged (same as in fixture)
        $expected = [
            'id' => '1',
            'first_name' => 'Admin',
            'surname' => 'Example',
            'email' => 'admin@example.com',
        ];

        // Assert that content of selected fields (which are the keys of the $expected array) are same as expected
        $this->assertTableRow($expected, 'user', 1, array_keys($expected));
    }


    /**
     * Empty or malformed request body is when parameters
     * are not set or have the wrong name ("key").
     * Example: Server needs the argument "email" to process
     * the request but "email" is not present in the body or
     * misspelled.
     * Good: "email: valid_or_invalid@data.com"
     * Bad: "emal: valid_or_invalid@data.com"
     *
     * If the request contains a different body than expected, HttpBadRequestException
     * is thrown and an error page is displayed to the user because that means that
     * there is an error with the client sending the request that has to be fixed.
     *
     * @dataProvider \App\Test\Provider\User\UserDataProvider::malformedRequestBodyProvider()
     *
     * @param array|null $malformedBody null for the case that request body is null
     * @param string $message
     */
    public function testUpdateUser_malformedBody(null|array $malformedBody, string $message): void
    {
        // Test with required values not set
        $malformedRequest = $this->createFormRequest(
            'POST',
            $this->urlFor('register-submit'),
            $malformedBody
        );

        // Bad Request (400) means that the client sent the request wrongly; it's a client error
        $this->expectException(HttpBadRequestException::class);
        $this->expectExceptionMessage($message);
        $this->app->handle($malformedRequest);
    }
}
