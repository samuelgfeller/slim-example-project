<?php

namespace App\Test\TestCase\Client\Create;

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

class ClientCreateDropdownOptionsTest extends TestCase
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
     * client create form are the right ones for different
     * authenticated user roles.
     *
     * @param array $authenticatedUserRow authenticated user attributes containing the user_role_id
     * @param array $otherUserRow another user (that appears in dropdown) attributes containing the user_role_id
     * @param array $expectedUserNames
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     *
     * @return void
     */
    #[DataProviderExternal(ClientCreateProvider::class, 'clientCreationDropdownOptionsCases')]
    public function testClientCreateAssignedUserDropdownOptionsAuthorization(
        array $authenticatedUserRow,
        array $otherUserRow,
        array $expectedUserNames,
    ): void {
        // Insert authenticated user one other
        $this->insertUserFixtures($authenticatedUserRow, $otherUserRow);

        // Client statuses, sexes and vigilance levels are returned too but not tested here (authorization most important)

        // Simulate logged-in user with the same user as linked to client
        $this->container->get(SessionInterface::class)->set('user_id', $authenticatedUserRow['id']);

        $request = $this->createJsonRequest(
            'GET',
            $this->urlFor('client-create-dropdown'),
        );

        // Handle request after defining expected exceptions
        $response = $this->app->handle($request);

        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        $responseJson = $this->getJsonData($response);

        // Sort both arrays and remove keys
        $expectedValues = array_values($expectedUserNames);
        $responseValues = array_values($responseJson['users']);

        sort($expectedValues);
        sort($responseValues);

        self::assertEquals($expectedValues, $responseValues);
    }
}
