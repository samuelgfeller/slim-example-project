<?php

namespace App\Test\Integration\Authentication;

use App\Domain\User\Data\UserData;
use App\Test\Traits\AppTestTrait;
use App\Test\Fixture\UserFixture;
use App\Test\Traits\DatabaseExtensionTestTrait;
use App\Test\Traits\RouteTestTrait;
use Fig\Http\Message\StatusCodeInterface;
use Odan\Session\SessionInterface;
use PHPUnit\Framework\TestCase;
use Selective\TestTrait\Traits\DatabaseTestTrait;
use Selective\TestTrait\Traits\MailerTestTrait;

/**
 * Test login submit actions. Contents of this test:
 *  - normal login submit with correct credentials (302 Found redirect)
 *  - login request with incorrect password (401 Unverified)
 *  - login request with invalid values (400 Bad request)
 *  - login request on unverified account (401 Unverified + email with verification token)
 *  - login request on suspended account (401 Unverified + email with info)
 *  - login request on locked account (401 Unverified + email with unlock token)
 *
 * Login tests involving request throttle are done in @see SecurityActionTest
 */
class LoginSubmitActionTest extends TestCase
{
    use AppTestTrait;
    use DatabaseTestTrait;
    use DatabaseExtensionTestTrait;
    use RouteTestTrait;
    use MailerTestTrait;

    /**
     * Test successful login
     *
     * @dataProvider \App\Test\Provider\User\UserDataProvider::userLoginCredentialsProvider()
     * @param array $loginFormValues valid credentials
     */
    public function testLoginSubmitAction(array $loginFormValues): void
    {
        $this->insertFixtures([UserFixture::class]);

        // Create request
        $request = $this->createFormRequest('POST', $this->urlFor('login-submit'), $loginFormValues);

        $response = $this->app->handle($request);

        // Assert: 302 Found (redirect)
        self::assertSame(StatusCodeInterface::STATUS_FOUND, $response->getStatusCode());

        $session = $this->container->get(SessionInterface::class);
        // Assert that session user_id is set
        self::assertIsInt($session->get('user_id'));
    }

    /**
     * Test that 401 Unauthorized is returned when trying to log in
     * with wrong password
     */
    public function testLoginSubmitAction_wrongPassword(): void
    {
        $this->insertFixtures([UserFixture::class]);

        $invalidCredentials = [
            // Same keys than HTML form
            'email' => 'admin@example.com',
            'password' => 'wrong password',
        ];

        // Create request
        $request = $this->createFormRequest('POST', $this->urlFor('login-submit'), $invalidCredentials);

        $response = $this->app->handle($request);

        // Assert: 401 Unauthorized
        self::assertSame(StatusCodeInterface::STATUS_UNAUTHORIZED, $response->getStatusCode());

        $session = $this->container->get(SessionInterface::class);
        // Assert that session user_id is not set
        self::assertNull($session->get('user_id'));
    }

    /**
     * Test login with invalid values that must not pass validation.
     *
     * @dataProvider \App\Test\Provider\User\UserDataProvider::invalidLoginCredentialsProvider()
     *
     * @param array $invalidLoginValues valid credentials
     */
    public function testLoginSubmitAction_invalidValues(array $invalidLoginValues): void
    {
        $this->insertFixtures([UserFixture::class]);

        // Create request
        $request = $this->createFormRequest('POST', $this->urlFor('login-submit'), $invalidLoginValues);
        $response = $this->app->handle($request);

        // Assert: 422 Unauthorized
        self::assertSame(StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY, $response->getStatusCode());

        $session = $this->container->get(SessionInterface::class);
        // Assert that session user_id is not set
        self::assertNull($session->get('user_id'));
    }


    /**
     * Test login with user status unverified.
     * When account is unverified, a verification link is sent to the user via the email.
     *
     * @dataProvider \App\Test\Provider\User\UserDataProvider::userLoginCredentialsProvider()
     *
     * @param array $userCredentials valid credentials
     */
    public function testLoginSubmitAction_accountUnverified(array $userCredentials): void
    {
        $userRow = (new UserFixture())->records[1];
        $userRow['status'] = UserData::STATUS_UNVERIFIED;
        $this->insertFixture('user', $userRow);
        $userId = $userRow['id'];

        // Create request
        $request = $this->createFormRequest('POST', $this->urlFor('login-submit'), $userCredentials);
        $response = $this->app->handle($request);

        // Assert: 401 Unauthorized
        self::assertSame(StatusCodeInterface::STATUS_UNAUTHORIZED, $response->getStatusCode());

        // Assert that user is NOT authenticated
        $session = $this->container->get(SessionInterface::class);
        // Assert that session user_id is not set
        self::assertNull($session->get('user_id'));

        // When account is unverified, a verification link is sent to the user via the email

        // Assert that correct email was sent (email body contains string)
        $email = $this->getMailerMessage();
        $this->assertEmailHtmlBodyContains(
            $email,
            'If you just tried to log in, please take note that you have to validate your email address first.'
        );
        // Assert that email was sent to the right person in the right format
        $this->assertEmailHeaderSame($email, 'To', $userRow['first_name'] . ' ' .
            $userRow['surname'] . ' <' . $userCredentials['email'] . '>');

        // Assert that there is a verification token in the database
        $expectedVerificationToken = [
            // CakePHP Database returns always strings
            'user_id' => (string)$userId,
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
     * Test login with user status suspended.
     *
     * @dataProvider \App\Test\Provider\User\UserDataProvider::userLoginCredentialsProvider()
     *
     * @param array $userCredentials valid credentials
     */
    public function testLoginSubmitAction_accountSuspended(array $userCredentials): void
    {
        $userRow = (new UserFixture())->records[1];
        $userRow['status'] = UserData::STATUS_SUSPENDED;
        $this->insertFixture('user', $userRow);
        $userId = $userRow['id'];

        // Create request
        $request = $this->createFormRequest('POST', $this->urlFor('login-submit'), $userCredentials);
        $response = $this->app->handle($request);

        // Assert: 401 Unauthorized
        self::assertSame(StatusCodeInterface::STATUS_UNAUTHORIZED, $response->getStatusCode());

        // Assert that user is NOT authenticated
        $session = $this->container->get(SessionInterface::class);
        // Assert that session user_id is not set
        self::assertNull($session->get('user_id'));

        // Assert that correct email was sent (email body contains string)
        $email = $this->getMailerMessage();
        $this->assertEmailHtmlBodyContains(
            $email,
            'If you just tried to log in, please take note that your account is suspended.'
        );
        // Assert that email was sent to the right person in the right format
        $this->assertEmailHeaderSame($email, 'To', $userRow['first_name'] . ' ' .
            $userRow['surname'] . ' <' . $userCredentials['email'] . '>');
    }

    /**
     * Test login with user status suspended.
     *
     * @dataProvider \App\Test\Provider\User\UserDataProvider::userLoginCredentialsProvider()
     *
     * @param array $userCredentials valid credentials
     */
    public function testLoginSubmitAction_accountLocked(array $userCredentials): void
    {
        $userRow = (new UserFixture())->records[1];
        $userRow['status'] = UserData::STATUS_LOCKED;
        $this->insertFixture('user', $userRow);
        $userId = $userRow['id'];

        // Create request
        $request = $this->createFormRequest('POST', $this->urlFor('login-submit'), $userCredentials);
        $response = $this->app->handle($request);

        // Assert: 401 Unauthorized
        self::assertSame(StatusCodeInterface::STATUS_UNAUTHORIZED, $response->getStatusCode());

        // Assert that user is NOT authenticated
        $session = $this->container->get(SessionInterface::class);
        // Assert that session user_id is not set
        self::assertNull($session->get('user_id'));

        // When account is locked, a verification link is sent to the user via the email to unlock account

        // Assert that correct email was sent (email body contains string)
        $email = $this->getMailerMessage();
        $this->assertEmailHtmlBodyContains(
            $email,
            'If you just tried to log in, please take note that your account is locked.'
        );
        // Assert that email was sent to the right person in the right format
        $this->assertEmailHeaderSame($email, 'To', $userRow['first_name'] . ' ' .
            $userRow['surname'] . ' <' . $userCredentials['email'] . '>');
    }
}
