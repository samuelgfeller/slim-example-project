<?php

namespace App\Test\Integration\Client;

use App\Domain\Client\Enum\ClientStatus;
use App\Domain\User\Enum\UserActivity;
use App\Test\Fixture\ClientStatusFixture;
use App\Test\Traits\AppTestTrait;
use App\Test\Traits\AuthorizationTestTrait;
use App\Test\Traits\DatabaseExtensionTestTrait;
use App\Test\Traits\FixtureTestTrait;
use Fig\Http\Message\StatusCodeInterface;
use PHPUnit\Framework\TestCase;
use Selective\TestTrait\Traits\DatabaseTestTrait;
use Selective\TestTrait\Traits\HttpJsonTestTrait;
use Selective\TestTrait\Traits\HttpTestTrait;
use Selective\TestTrait\Traits\RouteTestTrait;
use Slim\Exception\HttpBadRequestException;

/**
 * Client creation from public page submit tests.
 */
class ApiClientCreateActionTest extends TestCase
{
    use AppTestTrait;
    use HttpTestTrait;
    use HttpJsonTestTrait;
    use RouteTestTrait;
    use DatabaseTestTrait;
    use DatabaseExtensionTestTrait;
    use FixtureTestTrait;
    use AuthorizationTestTrait;

    /**
     * Client creation from api endpoint with valid data.
     * Test CORS headers.
     *
     * @throws \JsonException
     *
     * @return void
     */
    public function testApiClientSubmitCreateAction(): void
    {
        // Insert required client status
        $clientStatusId = $this->insertFixturesWithAttributes(
            ['name' => ClientStatus::ACTION_PENDING->value],
            ClientStatusFixture::class
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
        $allowedOriginUrl = $this->container->get('settings')['api']['allowed_origin'] ?? '';
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
                    array_merge(
                        $clientCreationValues,
                        ['vigilance_level' => null, 'user_id' => null, 'client_status_id' => $clientStatusId],
                    ),
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
     * @dataProvider \App\Test\Provider\Client\ApiClientCreateProvider::invalidApiClientCreationValues()
     *
     * @param array $requestBody
     * @param array $jsonResponse
     *
     * @return void
     */
    public function testApiClientSubmitCreateActionInvalid(
        array $requestBody,
        array $jsonResponse
    ): void {
        // Insert required client status which is set by the service function
        $clientStatusId = $this->insertFixturesWithAttributes(
            ['name' => ClientStatus::ACTION_PENDING->value],
            ClientStatusFixture::class
        )['id'];

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

    /**
     * Test client creation with malformed request body.
     *
     * @dataProvider \App\Test\Provider\Client\ApiClientCreateProvider::malformedRequestBodyCases()
     *
     * @param array $requestBody
     *
     * @return void
     */
    public function testApiClientSubmitCreateActionMalformedRequestBody(
        array $requestBody
    ): void {
        $request = $this->createJsonRequest(
            'POST',
            $this->urlFor('api-client-create-submit'),
            $requestBody
        );

        // Bad Request (400) means that the client sent the request wrongly; it's a frontend error
        $this->expectException(HttpBadRequestException::class);
        $this->expectExceptionMessage('Request body malformed.');

        // Handle request after defining expected exceptions
        $this->app->handle($request);
    }
}
