<?php

namespace App\Test\Integration\User;

use App\Domain\User\Enum\UserLang;
use App\Domain\User\Enum\UserStatus;
use App\Test\Fixture\UserFixture;
use App\Test\Trait\AppTestTrait;
use App\Test\Trait\AuthorizationTestTrait;
use Fig\Http\Message\StatusCodeInterface;
use Odan\Session\SessionInterface;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use TestTraits\Trait\DatabaseTestTrait;
use TestTraits\Trait\FixtureTestTrait;
use TestTraits\Trait\HttpJsonTestTrait;
use TestTraits\Trait\HttpTestTrait;
use TestTraits\Trait\RouteTestTrait;

class UserCreateDropdownOptionsTest extends TestCase
{
    use AppTestTrait;
    use HttpTestTrait;
    use HttpJsonTestTrait;
    use RouteTestTrait;
    use DatabaseTestTrait;
    use FixtureTestTrait;
    use AuthorizationTestTrait;

    /**
     * Test that the dropdown options fetched when opening the
     * user create form are the right ones for different
     * authenticated user roles.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     *
     * @return void
     */
    #[DataProviderExternal(\App\Test\Provider\User\UserCreateProvider::class, 'userCreationDropdownOptionsCases')]
    public function testUserCreateDropdownOptionsAuthorization(
        array $authenticatedUserAttributes,
        array $expectedUserRoles,
    ): void {
        // Insert authenticated user one other
        $authenticatedUserRow = $this->insertFixture(
            UserFixture::class,
            $this->addUserRoleId($authenticatedUserAttributes)
        );

        // Simulate logged-in user with same user as linked to client
        $this->container->get(SessionInterface::class)->set('user_id', $authenticatedUserRow['id']);

        $request = $this->createJsonRequest(
            'GET',
            $this->urlFor('user-create-dropdown'),
        );

        // Handle request after defining expected exceptions
        $response = $this->app->handle($request);

        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        $responseJson = $this->getJsonData($response);
        // Assert equals without taking into account user id as it is not known in data provider
        self::assertEqualsCanonicalizing($expectedUserRoles, $responseJson['userRoles']);

        // Assert statuses
        self::assertEqualsCanonicalizing(UserStatus::getAllDisplayNames(), $responseJson['statuses']);

        // Assert languages
        self::assertEqualsCanonicalizing(UserLang::toArray(), $responseJson['languages']);
    }
}
