<?php

namespace App\Test\Integration\Actions\User;

use App\Test\AppTestTrait;
use App\Test\Fixture\UserFixture;
use Fig\Http\Message\StatusCodeInterface;
use Odan\Session\SessionInterface;
use PHPUnit\Framework\TestCase;
use Selective\TestTrait\Traits\DatabaseTestTrait;
use Selective\TestTrait\Traits\HttpJsonTestTrait;
use Selective\TestTrait\Traits\HttpTestTrait;
use Selective\TestTrait\Traits\RouteTestTrait;

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

        $request = $this->createJsonRequest(
            'PUT',
            // Request to change user with id 1
            $this->urlFor('user-update-submit', ['user_id' => 1]),
            // Same keys than HTML form
            [
                'name' => 'Admin Example edited',
                'email' => 'edited@samuel-gfeller.ch'
            ]
        );

        $response = $this->app->handle($request);

        // Assert: 200 OK
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        $expected = [
            // id is string as CakePHP Database returns always strings: https://stackoverflow.com/a/11913488/9013718
            'id' => '1',
            'name' => 'Admin Example edited',
            'email' => 'edited@samuel-gfeller.ch',
            // Not password since the hash value always changes, it's asserted later
        ];

        // Assert that content of selected fields (which are the keys of the $expected array) are same as expected
        $this->assertTableRow($expected, 'user', 1, array_keys($expected));

        // Assert that field "id" of row with id 1 equals to "1" (CakePHP returns always strings)
        $this->assertTableRowValue('1', 'user', 1, 'id');
    }

    /**
     * Test behaviour when trying to exist a user that doesn't exist
     */
    public function testUpdateUser_invalidData(): void
    {
        $this->insertFixtures([UserFixture::class]);

        // Simulate logged in user with id 1
        $this->container->get(SessionInterface::class)->set('user_id', 1);

        $request = $this->createJsonRequest(
            'PUT',
            // Request to change non-existent user with id 999
            $this->urlFor('user-update-submit', ['user_id' => 999]),
            // Same keys than HTML form
            [
                'name' => 'A',
                'email' => 'edited@samuel-gf$eller.ch'
            ]
        );

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
                            'field' => 'name',
                            'message' => 'Required minimum length is 2',
                        ],
                        2 => [
                            'field' => 'email',
                            'message' => 'Invalid email address',
                        ],
                    ],
                ],
            ],
            $response
        );
    }

//    /**
//     * Test invalid user creation
//     * When validation fails the ValidationException is caught and errors
//     * are given to phpRenderer as attribute called $validation.
//     *
//     * This is not possible to test however because I can’t just access the body of a
//     * response object. The content is in a so called “stream”. Well I could with __toString()
//     * a plain string doesn't help for testing.
//     *
//     * That’s why I will only assert that the Response status is the right one on a validation
//     * exception (422) or 400 Bad request
//     */
//    public function testRegisterUser_invalidData(): void
//    {
//        // Test with required values not set
//        $requestRequiredNotSet = $this->createFormRequest(
//            'POST',
//            $this->urlFor('register-submit'),
//            // Same keys than HTML form
//            [
//                'name' => '',
//                'email' => '',
//                'password' => '',
//                'password2' => '',
//            ]
//        );
//
//        $responseRequiredNotSet = $this->app->handle($requestRequiredNotSet);
//        // Request body syntax is right and server understands the content but it's not able (doesn't want) to process it
//        // https://stackoverflow.com/a/3291292/9013718 422 Unprocessable Entity is the right status code
//        self::assertSame(StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY, $responseRequiredNotSet->getStatusCode());
//
//        $requestInvalid = $this->createFormRequest(
//            'POST',
//            $this->urlFor('register-submit'),
//            // Same keys than HTML form
//            [
//                'name' => 'Ad',
//                /* Name too short */ 'email' => 'admi$n@exampl$e.com',
//                /* Invalid E-Mail */ 'password' => '123',
//                /* Password too short */ 'password2' => '12',
//                /* Password 2 not matching and too short */
//            ]
//        );
//
//        $responseInvalid = $this->app->handle($requestInvalid);
//        // Assert that response has error status 422
//        self::assertSame(StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY, $responseInvalid->getStatusCode());
//    }
//
//    /**
//     * Empty or malformed request body is when parameters
//     * are not set or have the wrong name ("key").
//     * Example: Server needs the argument "email" to process
//     * the request but "email" is not present in the body or
//     * misspelled.
//     * Good: "email: valid_or_invalid@data.com"
//     * Bad: "emal: valid_or_invalid@data.com"
//     *
//     * If the request contains a different body than expected, HttpBadRequestException
//     * is thrown and an error page is displayed to the user because that means that
//     * there is an error with the client sending the request that has to be fixed.
//     *
//     * @dataProvider \App\Test\Provider\UserProvider::malformedRequestBodyProvider()
//     *
//     * @param array|null $malformedBody null for the case that request body is null
//     * @param string $message
//     */
//    public function testRegisterUser_malformedRequestBody(null|array $malformedBody, string $message): void
//    {
//        // Test with required values not set
//        $malformedRequest = $this->createFormRequest(
//            'POST',
//            $this->urlFor('register-submit'),
//            $malformedBody,
//        );
//
//        // Bad Request (400) means that the client sent the request wrongly; it's a client error
//        $this->expectException(HttpBadRequestException::class);
//        $this->expectExceptionMessage($message);
//        $this->app->handle($malformedRequest);
//    }
}
