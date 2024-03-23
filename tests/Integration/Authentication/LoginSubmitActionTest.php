<?php

namespace App\Test\Integration\Authentication;

use App\Domain\User\Enum\UserActivity;
use App\Domain\User\Enum\UserStatus;
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
 * Test the login submit actions. Contents of this test:
 *  - normal login submit with correct credentials (302 Found redirect)
 *  - login request with incorrect password (401 Unverified)
 *  - login request with invalid values (400 Bad request)
 *  - login request on unverified account (401 Unverified + email with verification token)
 *  - login request on suspended account (401 Unverified + email with info)
 *  - login request on locked account (401 Unverified + email with unlock token).
 *
 * Login tests involving request throttle are done in @see LoginSecurityTest
 */
class LoginSubmitActionTest extends TestCase
{
    use AppTestTrait;
    use HttpTestTrait;
    use DatabaseTestTrait;
    use RouteTestTrait;
    use MailerTestTrait;
    use FixtureTestTrait;

    /**
     * Test successful login.
     */
    public function testLoginSubmitAction(): void
    {
        $loginValues = ['password' => '12345678', 'email' => 'user@example.com'];
        $userRow = $this->insertFixture(
            new UserFixture(),
            [
                'password_hash' => password_hash($loginValues['password'], PASSWORD_DEFAULT),
                'email' => $loginValues['email'],
            ],
        );

        // Create request
        $request = $this->createFormRequest('POST', $this->urlFor('login-submit'), $loginValues);
        $response = $this->app->handle($request);

        // Assert: 302 Found (redirect)
        self::assertSame(StatusCodeInterface::STATUS_FOUND, $response->getStatusCode());

        // Assert that session user_id is set
        self::assertSame($userRow['id'], $this->container->get(SessionInterface::class)->get('user_id'));

        // Assert that user activity is inserted
        $this->assertTableRow(
            [
                'action' => UserActivity::READ->value,
                'table' => 'user',
                'row_id' => $userRow['id'],
                'data' => json_encode(['login'], JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR),
            ],
            'user_activity',
            (int)$this->findLastInsertedTableRow('user_activity')['id']
        );
    }

    /**
     * Test that 401 Unauthorized is returned when trying to log in
     * with wrong password.
     */
    public function testLoginSubmitActionWrongPassword(): void
    {
        $this->insertFixture(new UserFixture());

        $invalidCredentials = [
            'email' => 'admin@test.com',
            'password' => 'wrong password',
        ];

        // Create request
        $request = $this->createFormRequest('POST', $this->urlFor('login-submit'), $invalidCredentials);
        $response = $this->app->handle($request);

        // Assert: 401 Unauthorized
        self::assertSame(StatusCodeInterface::STATUS_UNAUTHORIZED, $response->getStatusCode());

        // Assert that session user_id is not set
        self::assertNull($this->container->get(SessionInterface::class)->get('user_id'));

        // Get response body as string from stream
        $stream = $response->getBody();
        $stream->rewind();
        $body = $stream->getContents();

        // Assert that response body contains validation error
        self::assertStringContainsString('Invalid credentials', $body);
    }

    /**
     * Test login with invalid values that must not pass validation.
     *
     * @param array $invalidLoginValues valid credentials
     * @param string $errorMessage validation message that should be in response body
     */
    #[DataProviderExternal(\App\Test\Provider\Authentication\AuthenticationProvider::class, 'invalidLoginCredentialsProvider')]
    public function testLoginSubmitActionInvalidValues(array $invalidLoginValues, string $errorMessage): void
    {
        $this->insertFixture(new UserFixture());

        // Create request
        $request = $this->createFormRequest('POST', $this->urlFor('login-submit'), $invalidLoginValues);
        $response = $this->app->handle($request);

        // Assert: 422 Unauthorized
        self::assertSame(StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY, $response->getStatusCode());

        $session = $this->container->get(SessionInterface::class);
        // Assert that session user_id is not set
        self::assertNull($session->get('user_id'));

        // Get response body as string from stream
        $stream = $response->getBody();
        $stream->rewind();
        $body = $stream->getContents();

        // Assert that response body contains validation error
        self::assertStringContainsString($errorMessage, $body);
    }

    /**
     * Test login with user status unverified.
     * When account is unverified, a verification link is sent to the user via the email.
     *
     * @param UserStatus $status
     * @param string $partialEmailBody
     */
    #[DataProviderExternal(\App\Test\Provider\Authentication\AuthenticationProvider::class, 'nonActiveAuthenticationRequestCases')]
    public function testLoginSubmitActionNotActiveAccount(UserStatus $status, string $partialEmailBody): void
    {
        $loginValues = ['password' => '12345678', 'email' => 'user@example.com'];
        $userRow = $this->insertFixture(
            new UserFixture(),
            [
                'password_hash' => password_hash($loginValues['password'], PASSWORD_DEFAULT),
                'email' => $loginValues['email'],
                'status' => $status->value,
            ],
        );

        // Create request
        $request = $this->createFormRequest('POST', $this->urlFor('login-submit'), $loginValues);
        $response = $this->app->handle($request);

        // Assert: 401 Unauthorized
        self::assertSame(StatusCodeInterface::STATUS_UNAUTHORIZED, $response->getStatusCode());

        // Assert that user is NOT authenticated
        $session = $this->container->get(SessionInterface::class);
        // Assert that session user_id is not set
        self::assertNull($session->get('user_id'));

        // When the account is unverified, a verification link is sent to the user via the email
        // Assert that correct email was sent (email body contains string)
        $email = $this->getMailerMessage();
        $this->assertEmailHtmlBodyContains(
            $email,
            $partialEmailBody,
        );
        // Assert that email was sent to the right person in the right format
        $this->assertEmailHeaderSame(
            $email,
            'To',
            $userRow['first_name'] . ' ' .
            $userRow['surname'] . ' <' . $loginValues['email'] . '>'
        );

        // Assert that there is a verification token in the database if unverified or locked
        if ($status === UserStatus::Unverified || $status === UserStatus::Locked) {
            $expectedVerificationToken = [
                'user_id' => $userRow['id'],
                'used_at' => null,
                'deleted_at' => null,
            ];
            $this->assertTableRowsByColumn(
                $expectedVerificationToken,
                'user_verification',
                'user_id',
                $userRow['id']
            );

            // Get user_verification row to make sure it has valid expiration time
            $userVerificationRow = $this->findTableRowsByColumn('user_verification', 'user_id', $userRow['id'])[0];

            // Assert that token expiration date is at least 59min the future
            self::assertTrue($userVerificationRow['expires_at'] > (time() + 60 * 59));

            // Assert that user activity is inserted
            $this->assertTableRow(
                [
                    'action' => UserActivity::CREATED->value,
                    'table' => 'user_verification',
                    'row_id' => (int)$this->findLastInsertedTableRow('user_verification')['id'],
                    // Data not asserted
                ],
                'user_activity',
                (int)$this->findLastInsertedTableRow('user_activity')['id']
            );
        }
    }
}
