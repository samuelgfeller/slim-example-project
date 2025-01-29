<?php

namespace App\Test\TestCase\Authentication\PasswordReset;

use App\Test\Fixture\UserFixture;
use App\Test\Trait\AppTestTrait;
use Fig\Http\Message\StatusCodeInterface;
use Odan\Session\SessionInterface;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use TestTraits\Trait\DatabaseTestTrait;
use TestTraits\Trait\FixtureTestTrait;
use TestTraits\Trait\HttpTestTrait;
use TestTraits\Trait\MailerTestTrait;
use TestTraits\Trait\RouteTestTrait;

/**
 * Integration testing email submit of forgotten password form
 *  - submit valid email
 *  - submit valid email after email abuse threshold reached -> unprocessable entity
 *  - submit email of non-existing user -> act normal but no database change
 *  - submit invalid email -> 422 backend validation fails.
 */
class PasswordForgottenEmailSubmitActionTest extends TestCase
{
    use AppTestTrait;
    use HttpTestTrait;
    use MailerTestTrait;
    use RouteTestTrait;
    use DatabaseTestTrait;
    use FixtureTestTrait;

    /**
     * Request to change password.
     */
    public function testPasswordForgottenEmailSubmit(): void
    {
        // Insert user
        $userRow = $this->insertFixture(UserFixture::class);

        // Simulate logged-in user
        $this->container->get(SessionInterface::class)->set('user_id', $userRow['id']);

        $request = $this->createFormRequest(
            'POST', // Request to change password
            $this->urlFor('password-forgotten-email-submit'),
            ['email' => $userRow['email']]
        );

        $response = $this->app->handle($request);

        // Assert: 302 Found (redirect)
        self::assertSame(StatusCodeInterface::STATUS_FOUND, $response->getStatusCode());

        // --- Email assertions ---
        // Assert that an email was sent
        $this->assertEmailCount(1);
        // Get email RawMessage
        $mailerMessage = $this->getMailerMessage();
        // Assert that the email contains the right text
        $this->assertEmailHtmlBodyContains(
            $mailerMessage,
            'If you recently requested to reset your password, click the link below to do so.'
        );
        // Assert that the right email has been sent to the right person
        $this->assertEmailHeaderSame(
            $mailerMessage,
            'To',
            $userRow['first_name'] . ' ' . $userRow['last_name'] . ' <' . $userRow['email'] . '>'
        );

        // --- Database assertions ---
        // Assert that there is a verification token in the database
        $expectedVerificationToken = [
            // CakePHP Database returns always strings
            'user_id' => $userRow['id'],
            'used_at' => null,
            'deleted_at' => null,
        ];
        $this->assertTableRowsByColumn(
            $expectedVerificationToken,
            'user_verification',
            'user_id',
            $userRow['id'],
            array_keys($expectedVerificationToken)
        );

        // Get user_verification row to make sure its valid
        $userVerificationRow = $this->findTableRowsByColumn('user_verification', 'user_id', $userRow['id'])[0];

        // Assert that token expiration date is at least 59 min the future
        self::assertTrue($userVerificationRow['expires_at'] > (time() + 60 * 59));

        // Assert that token starts with the beginning of a BCRYPT hash
        self::assertStringStartsWith(
            '$2y$10$',
            $userVerificationRow['token'],
            'token not starting with beginning of bcrypt hash'
        );
    }

    /**
     * Assert that verification email is not sent if the email threshold is reached.
     * Using the same provider as the general security email test.
     *
     * @param int|string $delay
     * @param int $emailLogAmountInTimeSpan
     * @param array $securitySettings
     *
     * @return void
     */
    #[DataProviderExternal(\App\Test\TestCase\Security\Provider\EmailRequestProvider::class, 'individualEmailThrottlingTestCases')]
    public function testPasswordForgottenEmailSubmitSecurityThrottling(
        int|string $delay,
        int $emailLogAmountInTimeSpan,
        array $securitySettings,
    ): void {
        // Insert user
        $userRow = $this->insertFixture(UserFixture::class);
        $userId = $userRow['id'];
        $email = $userRow['email'];

        // Insert max amount of email log entries before throttling
        for ($i = 0; $i < $emailLogAmountInTimeSpan; $i++) {
            $this->insertFixtureRow(
                'email_log',
                ['to_email' => $email, 'created_at' => (new \DateTime())->format('Y-m-d H:i:s')]
            );
        }

        // Simulate logged-in user
        $this->container->get(SessionInterface::class)->set('user_id', $userRow['id']);

        $request = $this->createFormRequest(
            'POST', // Request to change password
            $this->urlFor('password-forgotten-email-submit'),
            [
                'email' => $email,
            ]
        );

        $response = $this->app->handle($request);

        // Assert: 422 Unprocessable Entity
        self::assertSame(StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY, $response->getStatusCode());

        // Email assertions
        // Assert email was not sent
        $this->assertEmailCount(0);

        // Database assertions

        // Assert that there is no verification token in the database
        $this->assertTableRowCount(0, 'user_verification');
    }

    /**
     * Test that nothing special is done if user does not exist.
     */
    public function testPasswordForgottenEmailSubmitUserNotExisting(): void
    {
        // Not inserting a user as it shouldn't exist

        $request = $this->createFormRequest(
            'POST', // Request to change password
            $this->urlFor('password-forgotten-email-submit'),
            [
                'email' => 'user@example.com',
            ]
        );

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
        // Insert user
        $userRow = $this->insertFixture(UserFixture::class);

        // Simulate logged-in user
        $this->container->get(SessionInterface::class)->set('user_id', $userRow['id']);

        $request = $this->createFormRequest(
            'POST', // Request to change password
            $this->urlFor('password-forgotten-email-submit'),
            [
                'email' => 'inval$d@ema$l.com',
            ]
        );

        $response = $this->app->handle($request);

        // Assert that response has error status 422
        self::assertSame(StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY, $response->getStatusCode());

        // Get response body as string from stream
        $stream = $response->getBody();
        $stream->rewind();
        $body = $stream->getContents();

        // Assert that response body contains validation error
        self::assertStringContainsString('Invalid email', $body);
    }
}
