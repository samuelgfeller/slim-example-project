<?php

namespace App\Test\Integration\Client;

use App\Test\Fixture\ClientFixture;
use App\Test\Fixture\ClientStatusFixture;
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

/**
 * Test cases for client read page load
 *  - authorization
 *  - unauthenticated.
 */
class ClientReadPageActionTest extends TestCase
{
    use AppTestTrait;
    use HttpTestTrait;
    use RouteTestTrait;
    use DatabaseTestTrait;
    use FixtureTestTrait;
    use AuthorizationTestTrait;

    #[DataProviderExternal(\App\Test\Provider\Client\ClientReadProvider::class, 'clientReadAuthorizationCases')]
    public function testClientReadPageActionAuthorization(
        array $userRow,
        array $authenticatedUserRow,
        bool $clientIsDeleted,
        int $expectedStatusCode,
    ): void {
        // Insert tested and authenticated user
        $this->insertUserFixtures($authenticatedUserRow, $userRow);

        // Insert linked client status
        $clientStatusId = $this->insertFixture(ClientStatusFixture::class)['id'];
        // Insert client linked to user to be sure that the user is permitted to see the client read page
        $clientRow = $this->insertFixture(
            ClientFixture::class,
            [
                'user_id' => $userRow['id'],
                'client_status_id' => $clientStatusId,
                'deleted_at' => $clientIsDeleted ? (new \DateTime())->format('Y-m-d H:i:s') : null,
            ],
        );

        // Simulate logged-in user by setting the user_id session variable
        $this->container->get(SessionInterface::class)->set('user_id', $authenticatedUserRow['id']);

        $request = $this->createRequest('GET', $this->urlFor('client-read-page', ['client_id' => $clientRow['id']]));

        $response = $this->app->handle($request);

        // Assert 200 OK - code only reaches here if no exception is thrown
        self::assertSame($expectedStatusCode, $response->getStatusCode());
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
