<?php

namespace App\Test\Integration\User;

use App\Test\Fixture\UserFixture;
use App\Test\Trait\AppTestTrait;
use App\Test\Trait\AuthorizationTestTrait;
use Fig\Http\Message\StatusCodeInterface;
use Odan\Session\SessionInterface;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use TestTraits\Trait\DatabaseTestTrait;
use TestTraits\Trait\FixtureTestTrait;
use TestTraits\Trait\HttpTestTrait;
use TestTraits\Trait\RouteTestTrait;

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
     * @param array $userRow user attributes containing the user_role_id
     * @param array $authenticatedUserRow authenticated user attributes containing the user_role_id
     * @param array $expectedResult
     *
     * @return void
     */
    #[DataProviderExternal(\App\Test\Provider\User\UserReadProvider::class, 'userReadAuthorizationCases')]
    public function testUserReadPageActionAuthorization(
        array $userRow,
        array $authenticatedUserRow,
        array $expectedResult,
    ): void {
        // Insert tested and authenticated user
        $this->insertUserFixtures($authenticatedUserRow, $userRow);

        // Simulate logged-in user by setting the user_id session variable
        $this->container->get(SessionInterface::class)->set('user_id', $authenticatedUserRow['id']);

        $request = $this->createRequest('GET', $this->urlFor('user-read-page', ['user_id' => $userRow['id']]));

        $response = $this->app->handle($request);

        // Assert 200 OK - code only reaches here if no exception is thrown
        self::assertSame($expectedResult[StatusCodeInterface::class], $response->getStatusCode());
    }

    /**
     * Test that http not found exception is thrown when
     * user tries to read non-existing user page.
     *
     * @return void
     */
    public function testUserReadPageActionNotExisting(): void
    {
        $userRow = $this->insertFixture(UserFixture::class);

        // Simulate logged-in user by setting the user_id session variable
        $this->container->get(SessionInterface::class)->set('user_id', $userRow['id']);

        $request = $this->createRequest('GET', $this->urlFor('user-read-page', ['user_id' => '99']));

        $this->expectException(\Slim\Exception\HttpNotFoundException::class);

        $response = $this->app->handle($request);
    }

    /**
     * Test that user has to be logged in to display the page.
     *
     * @return void
     */
    public function testUserReadPageActionUnauthenticated(): void
    {
        // Request route to user read page while not being logged in
        $requestRoute = $this->urlFor('user-read-page', ['user_id' => '1']);
        $request = $this->createRequest('GET', $requestRoute);
        $response = $this->app->handle($request);
        // Assert 302 Found redirect to login url
        self::assertSame(StatusCodeInterface::STATUS_FOUND, $response->getStatusCode());

        // Build expected login url with redirect to initial request route as UserAuthenticationMiddleware.php does
        $expectedLoginUrl = $this->urlFor('login-page', [], ['redirect' => $requestRoute]);
        self::assertSame($expectedLoginUrl, $response->getHeaderLine('Location'));
    }
}
