<?php

namespace App\Test\Integration\User;

use App\Domain\User\Enum\UserRole;
use App\Test\Fixture\UserActivityFixture;
use App\Test\Fixture\UserFixture;
use App\Test\Trait\AppTestTrait;
use App\Test\Trait\AuthorizationTestTrait;
use Fig\Http\Message\StatusCodeInterface;
use Odan\Session\SessionInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use TestTraits\Trait\DatabaseTestTrait;
use TestTraits\Trait\FixtureTestTrait;
use TestTraits\Trait\HttpJsonTestTrait;
use TestTraits\Trait\HttpTestTrait;
use TestTraits\Trait\RouteTestTrait;

class UserFetchActivityActionTest extends TestCase
{
    use AppTestTrait;
    use HttpTestTrait;
    use RouteTestTrait;
    use HttpJsonTestTrait;
    use DatabaseTestTrait;
    use FixtureTestTrait;
    use AuthorizationTestTrait;

    /**
     * Test that when fetching user activity an array is returned.
     *
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     *
     * @return void
     */
    public function testUserListActivityFetchAction(): void
    {
        // Insert authenticated but unauthorized user newcomer
        $userId = $this->insertFixture(
            new UserFixture(),
            $this->addUserRoleId(['user_role_id' => UserRole::NEWCOMER]),
        )['id'];
        // Insert user activity to cover most possible code with this test
        $this->insertFixture(new UserActivityFixture(), ['user_id' => $userId]);

        // Simulate logged-in user by setting the user_id session variable
        $this->container->get(SessionInterface::class)->set('user_id', $userId);

        $request = $this->createJsonRequest('GET', $this->urlFor('user-get-activity'))
            ->withQueryParams(['user' => $userId]);
        $response = $this->app->handle($request);
        // Assert status code 200 OK
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
        // Get response as array
        $responseData = $this->getJsonData($response);
        // Only assert if array is returned as there is quite a logic how its built and I don't think its
        // pertinent to test this in detail and when it's changed it'd be annoying having to update it each time here
        self::assertIsArray($responseData);
        self::assertNotEmpty($responseData);
    }

    /**
     * Test that when fetching user activity without query params
     * an empty array is returned.
     *
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     *
     * @return void
     */
    public function testUserListActivityFetchActionWithoutQueryParams(): void
    {
        // Insert authenticated but unauthorized user newcomer
        $userId = $this->insertFixture(
            new UserFixture(),
            $this->addUserRoleId(['user_role_id' => UserRole::NEWCOMER]),
        )['id'];
        // Simulate logged-in user by setting the user_id session variable
        $this->container->get(SessionInterface::class)->set('user_id', $userId);

        $request = $this->createJsonRequest('GET', $this->urlFor('user-get-activity'));
        $response = $this->app->handle($request);
        // Assert status code 200 OK
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
        // Get response as array
        $responseData = $this->getJsonData($response);
        // Only assert if array is returned as there is quite a logic how its built and I don't think its
        // pertinent to test this in detail and when it's changed it'd be annoying having to update it each time here
        self::assertIsArray($responseData);
        self::assertEmpty($responseData);
    }
}
