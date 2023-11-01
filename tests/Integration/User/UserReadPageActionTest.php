<?php

namespace App\Test\Integration\User;

use App\Test\Traits\AppTestTrait;
use App\Test\Traits\AuthorizationTestTrait;
use App\Test\Traits\FixtureTestTrait;
use Fig\Http\Message\StatusCodeInterface;
use Odan\Session\SessionInterface;
use PHPUnit\Framework\TestCase;
use Selective\TestTrait\Traits\DatabaseTestTrait;
use Selective\TestTrait\Traits\HttpTestTrait;
use Selective\TestTrait\Traits\RouteTestTrait;

class UserReadPageActionTest extends TestCase
{
    use AppTestTrait;
    use HttpTestTrait;
    use RouteTestTrait;
    use DatabaseTestTrait;
    use FixtureTestTrait;
    use AuthorizationTestTrait;

    /**
     * Normal page action while being authenticated.
     *
     * @dataProvider \App\Test\Provider\User\UserReadProvider::userReadAuthorizationCases()
     *
     * @param array $userData user attributes containing the user_role_id
     * @param array $authenticatedUserData authenticated user attributes containing the user_role_id
     * @param array $expectedResult
     *
     * @return void
     */
    public function testClientReadPageActionAuthorization(
        array $userData,
        array $authenticatedUserData,
        array $expectedResult,
    ): void {
        // Insert tested and authenticated user
        $this->insertUserFixturesWithAttributes($userData, $authenticatedUserData);

        // Simulate logged-in user with logged-in user id
        $this->container->get(SessionInterface::class)->set('user_id', $authenticatedUserData['id']);

        $request = $this->createRequest('GET', $this->urlFor('user-read-page', ['user_id' => $userData['id']]));

        $response = $this->app->handle($request);

        // Assert 200 OK - code only reaches here if no exception is thrown
        self::assertSame($expectedResult[StatusCodeInterface::class], $response->getStatusCode());
    }

    /**
     * Test that user has to be logged in to display the page.
     *
     * @return void
     */
    public function testClientReadPageActionUnauthenticated(): void
    {
        // Request route to client read page while not being logged in
        $requestRoute = $this->urlFor('client-read-page', ['client_id' => '1']);
        $request = $this->createRequest('GET', $requestRoute);
        $response = $this->app->handle($request);
        // Assert 302 Found redirect to login url
        self::assertSame(StatusCodeInterface::STATUS_FOUND, $response->getStatusCode());

        // Build expected login url with redirect to initial request route as UserAuthenticationMiddleware.php does
        $expectedLoginUrl = $this->urlFor('login-page', [], ['redirect' => $requestRoute]);
        self::assertSame($expectedLoginUrl, $response->getHeaderLine('Location'));
    }
}
