<?php

namespace App\Test\Integration\Actions\Authentication;

use App\Domain\Utility\Mailer;
use App\Test\Traits\AppTestTrait;
use App\Test\Fixture\RequestTrackFixture;
use App\Test\Fixture\UserFixture;
use App\Test\Traits\MailerAssertionsTrait;
use Fig\Http\Message\StatusCodeInterface;
use PHPUnit\Framework\TestCase;
use Selective\TestTrait\Traits\DatabaseTestTrait;
use Selective\TestTrait\Traits\HttpTestTrait;
use Selective\TestTrait\Traits\RouteTestTrait;
use Slim\Exception\HttpBadRequestException;
use Symfony\Component\Mailer\Transport\TransportInterface;

/**
 * Integration testing user registrations
 */
class RegisterSubmitActionTest extends TestCase
{

    use AppTestTrait;
    use HttpTestTrait;
    use RouteTestTrait;
    use DatabaseTestTrait;
    use MailerAssertionsTrait;

    /**
     * Test user creation
     *
     * @return void
     */
    public function testUserRegistration(): void
    {
        $name = 'Admin Example';
        $emailAddr = 'admin@example.com';
        $request = $this->createFormRequest(
            'POST',
            $this->urlFor('register-submit'),
            // Same keys than HTML form
            [
                'name' => $name,
                'email' => $emailAddr,
                'password' => '12345678',
                'password2' => '12345678',
            ]
        );

        $response = $this->app->handle($request);

        // Assert: 302 Found (redirect)
        self::assertSame(StatusCodeInterface::STATUS_FOUND, $response->getStatusCode());

        // Email assertions
        $email = $this->getMailerMessage();
        self::assertEmailHtmlBodyContains(
            $email,
            'To verify that this email address belongs to you, please click on the following link'
        );
        // Assert that right email has been sent to the right person
        self::assertEmailHeaderSame($email, 'To', $name . ' <' . $emailAddr . '>');

        // Database assertions
        $this->assertTableRowCount(1, 'user');

        $expected = [
            // id is string as CakePHP Database returns always strings: https://stackoverflow.com/a/11913488/9013718
            'id' => '1',
            'name' => 'Admin Example',
            'email' => 'admin@example.com',
            // Not password since the hash value always changes, it's asserted later
        ];

        // Assert that content of selected fields (which are the keys of the $expected array) are same as expected
        $this->assertTableRow($expected, 'user', 1, array_keys($expected));

        // Assert that field "id" of row with id 1 equals to "1" (CakePHP returns always strings)
        $this->assertTableRowValue('1', 'user', 1, 'id');

        // Password
        $password = $this->getTableRowById('user', 1)['password_hash'];
        // Assert that password_hash starts with the beginning of a BCRYPT hash
        // Hash algo may change in the future but it's not a big deal to update if tests fail
        self::assertStringStartsWith(
            '$2y$10$',
            $password,
            'password_hash not starting with beginning of bcrypt hash'
        );
        // Verify that hash matches the given password
        self::assertTrue(password_verify('12345678', $password));
    }

    /**
     * Test that user can't be registered twice but client should not notice any difference
     * For further tests, breakpoint can be set inside the register function
     * Fixture dependency:
     *      UserFixture: first fixture (user) has to be "Admin Example" with email "admin@example.com"
     * @dataProvider \App\Test\Integration\Actions\Authentication\Provider\RegisterCaseProvider::provideExistingUserStatusAndEmail()
     * @param string $existingUserStatus
     * @param string $partialEmailBody
     */
    public function testUserRegistration_alreadyExisting(string $existingUserStatus, string $partialEmailBody): void
    {
        // Insert existing admin user with given status
        // Get first record from UserFixture class
        $existingUser = (new UserFixture())->records[0];
        // Adapt status to provided status
        $existingUser['status'] = $existingUserStatus;
        $this->insertFixture('user', $existingUser);
        // User amount in fixture can be changed and it still can be asserted that after the action there's the same
        // amount of users in the db
        $userAmountInFixture = count((new UserFixture())->records);
        $name = 'Admin Example';
        $emailAddr = 'admin@example.com';
        $request = $this->createFormRequest(
            'POST',
            $this->urlFor('register-submit'),
            // Same keys than HTML form
            [
                'name' => $name,
                'email' => $emailAddr,
                'password' => '12345678',
                'password2' => '12345678',
            ]
        );

        $response = $this->app->handle($request);

        // Assert: 302 Found (redirect to verification info page)
        self::assertSame(StatusCodeInterface::STATUS_FOUND, $response->getStatusCode());

        // Email assertions
        $email = $this->getMailerMessage();
        self::assertEmailHtmlBodyContains(
            $email,
            $partialEmailBody
        );
        self::assertEmailHeaderSame($email, 'To', $name . ' <' . $emailAddr . '>');

        // Database assertions
        // Check that there are not more users in the database
        $this->assertTableRowCount(1, 'user');
    }

    /**
     * Test invalid user creation
     * When validation fails the ValidationException is caught and errors
     * are given to phpRenderer as attribute called $validation.
     *
     * This is not possible to test however because I can’t just access the body of a
     * response object. The content is in a so called “stream”. Well I could with __toString()
     * a plain string doesn't help for testing.
     *
     * That’s why I will only assert that the Response status is the right one on a validation
     * exception (422) or 400 Bad request
     */
    public function testRegisterUser_invalidData(): void
    {
        // Test with required values not set
        $requestRequiredNotSet = $this->createFormRequest(
            'POST',
            $this->urlFor('register-submit'),
            // Same keys than HTML form
            [
                'name' => '',
                'email' => '',
                'password' => '',
                'password2' => '',
            ]
        );

        $responseRequiredNotSet = $this->app->handle($requestRequiredNotSet);
        // Request body syntax is right and server understands the content but it's not able (doesn't want) to process it
        // https://stackoverflow.com/a/3291292/9013718 422 Unprocessable Entity is the right status code
        self::assertSame(StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY, $responseRequiredNotSet->getStatusCode());

        $requestInvalid = $this->createFormRequest(
            'POST',
            $this->urlFor('register-submit'),
            // Same keys than HTML form
            [
                'name' => 'Ad',
                /* Name too short */
                'email' => 'admi$n@exampl$e.com',
                /* Invalid E-Mail */
                'password' => '123',
                /* Password too short */
                'password2' => '12',
                /* Password 2 not matching and too short */
            ]
        );

        $responseInvalid = $this->app->handle($requestInvalid);
        // Assert that response has error status 422
        self::assertSame(StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY, $responseInvalid->getStatusCode());
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
     * @dataProvider \App\Test\Provider\UserProvider::malformedRequestBodyProvider()
     *
     * @param array|null $malformedBody null for the case that request body is null
     * @param string $message
     */
    public function testRegisterUser_malformedRequestBody(null|array $malformedBody, string $message): void
    {
        // Test with required values not set
        $malformedRequest = $this->createFormRequest(
            'POST',
            $this->urlFor('register-submit'),
            $malformedBody,
        );

        // Bad Request (400) means that the client sent the request wrongly; it's a client error
        $this->expectException(HttpBadRequestException::class);
        $this->expectExceptionMessage($message);
        $this->app->handle($malformedRequest);
    }
}
