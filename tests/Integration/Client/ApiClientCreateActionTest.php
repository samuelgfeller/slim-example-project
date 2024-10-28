<?php

namespace App\Test\Integration\Client;

use App\Domain\Client\Enum\ClientStatus;
use App\Domain\User\Enum\UserActivity;
use App\Test\Fixture\ClientStatusFixture;
use App\Test\Trait\AppTestTrait;
use App\Test\Trait\AuthorizationTestTrait;
use Fig\Http\Message\StatusCodeInterface;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use TestTraits\Trait\DatabaseTestTrait;
use TestTraits\Trait\FixtureTestTrait;
use TestTraits\Trait\HttpJsonTestTrait;
use TestTraits\Trait\HttpTestTrait;
use TestTraits\Trait\RouteTestTrait;

/**
 * Client creation from external frontend application tests.
 */
class ApiClientCreateActionTest extends TestCase
{
    use AppTestTrait;
    use HttpTestTrait;
    use HttpJsonTestTrait;
    use RouteTestTrait;
    use DatabaseTestTrait;
    use FixtureTestTrait;
    use AuthorizationTestTrait;

    /**
     * Client creation from api endpoint with valid data.
     * Test CORS headers.
     *
     * @throws \JsonException|ContainerExceptionInterface|NotFoundExceptionInterface
     *
     * @return void
     */
    public function testApiClientSubmitCreateAction(): void
    {
        // Insert required client status
        $clientStatusId = $this->insertFixture(
            ClientStatusFixture::class,
            ['name' => ClientStatus::ACTION_PENDING->value],
        )['id'];

        $clientCreationValues = [
            'first_name' => 'New',
            'last_name' => 'Client',
            'birthdate' => '2000-03-15',
            'location' => 'Basel',
            'phone' => '+41 77 222 22 22',
            'email' => 'new-user@email.com',
            'sex' => 'M',
            'client_message' => 'This is a client message.',
        ];

        // Make request
        $request = $this->createJsonRequest(
            'POST',
            $this->urlFor('api-client-create-submit'),
            $clientCreationValues
        );

        $response = $this->app->handle($request);
        // Assert response status code: 201 Created
        self::assertSame(StatusCodeInterface::STATUS_CREATED, $response->getStatusCode());
        // Get test allowed origin url for CORS test
        $allowedOriginUrl = $this->container->get('settings')['api']['allowed_origin'] ?? '*';
        // Test CORS header
        self::assertSame($allowedOriginUrl, $response->getHeaderLine('Access-Control-Allow-Origin'));

        // Assert that db record is created
        $clientDbRow = $this->findLastInsertedTableRow('client');
        // Assert that db entry corresponds to the given client creation values
        $this->assertTableRowEquals($clientCreationValues, 'client', $clientDbRow['id']);

        // Assert user activity
        // Add client_message to creation values as they are inserted in user_activity
        $this->assertTableRowEquals(
            [
                'action' => UserActivity::CREATED->value,
                'table' => 'client',
                'row_id' => $clientDbRow['id'],
                // I don't find it very pretty to re-create the array for a correct json as that would have to be
                // updated each time the function changes. If it becomes annoying: remove the key "data" from assertion.
                'data' => json_encode(
                    array_merge($clientCreationValues, ['client_status_id' => $clientStatusId]),
                    JSON_THROW_ON_ERROR
                ),
            ],
            'user_activity',
            (int)$this->findTableRowsByColumn('user_activity', 'table', 'client')[0]['id']
        );

        $this->assertJsonData(['status' => 'success', 'data' => null], $response);
    }

    /**
     * Test api client creation validation.
     *
     * @param array $requestBody
     * @param array $jsonResponse
     *
     * @return void
     */
    #[DataProviderExternal(\App\Test\Provider\Client\ApiClientCreateProvider::class, 'invalidApiClientCreationValues')]
    public function testApiClientSubmitCreateActionInvalid(
        array $requestBody,
        array $jsonResponse,
    ): void {
        // Insert action pending client status because it's needed by the service function
        $this->insertFixture(
            ClientStatusFixture::class,
            ['name' => ClientStatus::ACTION_PENDING->value],
        );

        $request = $this->createJsonRequest(
            'POST',
            $this->urlFor('api-client-create-submit'),
            $requestBody
        );

        $response = $this->app->handle($request);
        // Assert 422
        self::assertSame(StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY, $response->getStatusCode());

        // No client should have been created
        $this->assertTableRowCount(0, 'client');

        $this->assertJsonData($jsonResponse, $response);
    }
}
