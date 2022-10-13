<?php


namespace App\Test\Integration\Client;


use App\Test\Fixture\ClientStatusFixture;
use App\Test\Fixture\FixtureTrait;
use App\Test\Traits\AppTestTrait;
use App\Test\Fixture\UserFixture;
use App\Test\Traits\DatabaseExtensionTestTrait;
use Fig\Http\Message\StatusCodeInterface;
use Odan\Session\SessionInterface;
use PHPUnit\Framework\TestCase;
use Selective\TestTrait\Traits\DatabaseTestTrait;
use Selective\TestTrait\Traits\HttpJsonTestTrait;
use Selective\TestTrait\Traits\HttpTestTrait;
use App\Test\Traits\RouteTestTrait;
use Slim\Exception\HttpBadRequestException;

/**
 * Client creation submit tests
 *  - Normal client creation
 *  - With invalid values -> 422
 *  - With malformed request body -> Bad request exception
 */
class ClientCreateActionTest extends TestCase
{

    use AppTestTrait;
    use HttpTestTrait;
    use HttpJsonTestTrait;
    use RouteTestTrait;
    use DatabaseTestTrait;
    use DatabaseExtensionTestTrait;
    use FixtureTrait;

    /**
     * Client creation with valid data
     *
     * @return void
     */
    public function testClientCreateAction(): void
    {
        // Test client insert with user
        $userRow = $this->findRecordsFromFixtureWhere(['role' => 'user'], UserFixture::class)[0];
        $this->insertFixture('user', $userRow);
        $clientStatusRow = (new ClientStatusFixture())->records[0];
        $this->insertFixture('client_status', $clientStatusRow);

        $clientCreationValues = [
            'first_name' => 'New',
            'last_name' => 'Client',
            'birthdate' => '2000-03-15',
            'location' => 'Basel',
            'phone' => '+41 77 222 22 22',
            'email' => 'new-user@email.com',
            'sex' => 'M',
            'client_message' => 'This is a message submitted by the client directly.',
            'user_id' => $userRow['id'],
            'client_status_id' => $clientStatusRow['id'],
        ];

        // Simulate session
        $this->container->get(SessionInterface::class)->set('user_id', $userRow['id']);
        // Make request
        $request = $this->createJsonRequest(
            'POST',
            $this->urlFor('client-submit-create'),
            $clientCreationValues
        );
        $response = $this->app->handle($request);
        // Assert response status code: 201 Created
        self::assertSame(StatusCodeInterface::STATUS_CREATED, $response->getStatusCode());

        // Assert database
        // Find freshly inserted client
        $noteDbRow = $this->findLastInsertedTableRow('client');
        // Assert the row column values that were inserted
        $this->assertTableRow($clientCreationValues, 'client', (int)$noteDbRow['id']);

        $this->assertJsonData(['status' => 'success'], $response);
    }

    /**
     * Test client values validation.
     * Test data for cases is the same as client update.
     * It's not optimal as required values can't be tested but good enough for now.
     *
     * @dataProvider \App\Test\Provider\Client\ClientCreateUpdateCaseProvider::invalidClientValuesAndExpectedResponseData()
     * @return void
     */
    public function testClientSubmitCreateAction_invalid($requestBody, $jsonResponse): void
    {
        $userRow = $this->findRecordsFromFixtureWhere(['role' => 'user'], UserFixture::class)[0];
        $this->insertFixture('user', $userRow);
        $clientStatusRow = (new ClientStatusFixture())->records[0];
        $this->insertFixture('client_status', $clientStatusRow);

        // Simulate session
        $this->container->get(SessionInterface::class)->set('user_id', $userRow['id']);

        $request = $this->createJsonRequest(
            'POST',
            $this->urlFor('client-submit-create'),
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
     * Client creation with valid data
     *
     * @return void
     */
    public function testClientCreateAction_unauthenticated(): void
    {
        // Create request (no body needed as it shouldn't be interpreted anyway)
        $request = $this->createJsonRequest('POST', $this->urlFor('client-submit-create'), []);
        // Provide redirect to if unauthorized header to test if UserAuthenticationMiddleware returns correct login url
        $redirectAfterLoginRouteName = 'client-list-page';
        $request = $request->withAddedHeader('Redirect-to-route-name-if-unauthorized', $redirectAfterLoginRouteName);
        // Make request
        $response = $this->app->handle($request);
        // Assert response HTTP status code: 401 Unauthorized
        self::assertSame(StatusCodeInterface::STATUS_UNAUTHORIZED, $response->getStatusCode());
        // Build expected login url as UserAuthenticationMiddleware.php does
        $expectedLoginUrl = $this->urlFor('login-page', [], ['redirect' => $this->urlFor($redirectAfterLoginRouteName)]
        );
        // Assert that response contains correct login url
        $this->assertJsonData(['loginUrl' => $expectedLoginUrl], $response);
    }

    /**
     * Test client creation with malformed request body
     *
     * @dataProvider \App\Test\Provider\Client\ClientCreateCaseProvider::malformedRequestBody()
     * @return void
     */
    public function testClientSubmitCreateAction_malformedRequest($requestBody): void
    {
        // Action class should directly return error so only logged-in user has to be inserted
        $userData = (new UserFixture())->records[0];
        $this->insertFixture('user', $userData);

        // Simulate logged-in user with same user as linked to client
        $this->container->get(SessionInterface::class)->set('user_id', $userData['id']);

        $request = $this->createJsonRequest(
            'POST',
            $this->urlFor('client-submit-create'),
            $requestBody
        );

        // Bad Request (400) means that the client sent the request wrongly; it's a frontend error
        $this->expectException(HttpBadRequestException::class);
        $this->expectExceptionMessage('Request body malformed.');

        // Handle request after defining expected exceptions
        $this->app->handle($request);
    }

}
