<?php

namespace App\Test\Integration\Authentication;

use App\Test\Fixture\UserFixture;
use App\Test\Traits\AppTestTrait;
use App\Test\Traits\DatabaseExtensionTestTrait;
use App\Test\Traits\FixtureTestTrait;
use Fig\Http\Message\StatusCodeInterface;
use Odan\Session\SessionInterface;
use PHPUnit\Framework\TestCase;
use Selective\TestTrait\Traits\DatabaseTestTrait;
use Selective\TestTrait\Traits\HttpTestTrait;
use Selective\TestTrait\Traits\MailerTestTrait;
use Selective\TestTrait\Traits\RouteTestTrait;
use Slim\Exception\HttpBadRequestException;

/**
 * Integration testing email submit of forgotten password form
 *  - submit valid email
 *  - submit email of non-existing user -> act normal but no database change
 *  - submit invalid email -> 422 backend validation fail
 *  - submit malformed request body -> HttpBadRequestException.
 */
class PasswordForgottenEmailSubmitActionTest extends TestCase
{
    use AppTestTrait;
    use HttpTestTrait;
    use MailerTestTrait;
    use RouteTestTrait;
    use DatabaseTestTrait;
    use DatabaseExtensionTestTrait;
    use FixtureTestTrait;

    /**
     * Request to change password.
     */
    public function testPasswordForgottenEmailSubmit(): void
    {
        // Insert user
        $userRow = $this->insertFixturesWithAttributes([], UserFixture::class);
        $userId = $userRow['id'];
        $email = $userRow['email'];

        // Simulate logged-in user with id 2
        $this->container->get(SessionInterface::class)->set('user_id', $userId);

        $request = $this->createFormRequest('POST', // Request to change password
            $this->urlFor('password-forgotten-email-submit'), [
                'email' => $email,
            ]);

        $response = $this->app->handle($request);

        // Assert: 302 Found (redirect)
        self::assertSame(StatusCodeInterface::STATUS_FOUND, $response->getStatusCode());

        // Email assertions
        $mailerMessage = $this->getMailerMessage();
        $this->assertEmailHtmlBodyContains(
            $mailerMessage,
            'If you recently requested to reset your password, click the link below to do so.'
        );
        // Assert that right email has been sent to the right person
        $this->assertEmailHeaderSame(
            $mailerMessage,
            'To',
            $userRow['first_name'] . ' ' . $userRow['surname'] . ' <' . $email . '>'
        );

        // Database assertions

        // Assert that there is a verification token in the database
        $expectedVerificationToken = [
            // CakePHP Database returns always strings
            'user_id' => $userId,
            'used_at' => null,
            'deleted_at' => null,
        ];
        $this->assertTableRowsByColumn(
            $expectedVerificationToken,
            'user_verification',
            'user_id',
            $userId,
            array_keys($expectedVerificationToken)
        );

        // Get user_verification row to make sure its valid
        $userVerificationRow = $this->findTableRowsByColumn('user_verification', 'user_id', $userId)[0];

        // Assert that token expiration date is at least 59min the future
        self::assertTrue($userVerificationRow['expires_at'] > (time() + 60 * 59));

        // Assert that token starts with the beginning of a BCRYPT hash
        self::assertStringStartsWith(
            '$2y$10$',
            $userVerificationRow['token'],
            'token not starting with beginning of bcrypt hash'
        );
    }

    /**
     * Test that nothing special is done if user does not exist.
     */
    public function testPasswordForgottenEmailSubmitUserNotExisting(): void
    {
        // Not inserting user as it shouldn't exist

        $request = $this->createFormRequest('POST', // Request to change password
            $this->urlFor('password-forgotten-email-submit'), [
                'email' => 'user@example.com',
            ]);

        $response = $this->app->handle($request);

        // Assert that response redirected to login page as it would if the user existed
        self::assertSame(StatusCodeInterface::STATUS_FOUND, $response->getStatusCode());

        self::assertSame($this->urlFor('login-page'), $response->getHeaderLine('Location'));

        // No verification token should be inserted
        $this->assertTableRowCount(0, 'user_verification');
    }

    /**
     * Test that backend validation fails when email is invalid.
     */
    public function testPasswordForgottenEmailSubmitInvalidData(): void
    {
        // Insert user id 2 role: user
        $userRow = (new UserFixture())->records[1];
        $this->insertFixture('user', $userRow);

        // Simulate logged-in user with id 2
        $this->container->get(SessionInterface::class)->set('user_id', $userRow['id']);

        $request = $this->createFormRequest('POST', // Request to change password
            $this->urlFor('password-forgotten-email-submit'), [
                'email' => 'inval$d@ema$l.com',
            ]);

        $response = $this->app->handle($request);

        // Assert that response has error status 422
        self::assertSame(StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY, $response->getStatusCode());

        // As form is directly rendered with validation errors it's not possible to test them as response is a stream
        // There is a visual test in insomnia for this, but I couldn't manage to keep the login session
    }

    /**
     * Empty or malformed request body is when parameters are not set or have
     * the wrong name ("key").
     *
     * If the request contains a different body than expected, HttpBadRequestException
     * is thrown and an error page is displayed to the user because that means that
     * there is an error with the client sending the request that has to be fixed.
     */
    public function testChangePasswordMalformedBody(): void
    {
        // Not necessary to insert fixture as exception must be thrown in action
        $validEmail = 'user@example.com';
        $malformedRequest = $this->createFormRequest(
            'POST', // Request to change password
            $this->urlFor('password-forgotten-email-submit'),
            ['emal' => $validEmail]
        );

        // Bad Request (400) means that the client sent the request wrongly; it's a client error
        $this->expectException(HttpBadRequestException::class);
        $this->expectExceptionMessage('Request body malformed.');

        // Handle request after defining expected exceptions
        $this->app->handle($malformedRequest);
    }
}
