<?php

namespace App\Test\Integration\Authentication;

use App\Test\Traits\AppTestTrait;
use App\Test\Fixture\UserFixture;
use App\Test\Traits\RouteTestTrait;
use Fig\Http\Message\StatusCodeInterface;
use Hoa\Protocol\Bin\Resolve;
use http\Client\Response;
use Odan\Session\SessionInterface;
use PHPUnit\Framework\TestCase;
use Selective\TestTrait\Traits\DatabaseTestTrait;

class LoginSubmitActionTest extends TestCase
{
    use AppTestTrait;
    use DatabaseTestTrait;
    use RouteTestTrait;


    // All tests involving request throttle are done in SecurityActionTest.php

    /**
     * Test successful login
     *
     * @dataProvider \App\Test\Provider\User\UserDataProvider::correctLoginCredentialsProvider()
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


}
