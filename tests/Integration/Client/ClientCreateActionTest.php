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
     * @dataProvider \App\Test\Provider\Client\ClientCreateCaseProvider::provideUsersAndExpectedResultForClientCreation()
     *
     * @param array $userLinkedToClientAttr client owner attributes containing the user_role_id
     * @param array $authenticatedUserAttr authenticated user attributes containing the user_role_id
     * @param array $expectedResult HTTP status code, bool if db_entry_created and json_response
     * @return void
     */
    public function testClientSubmitCreateAction_authorization(
        array $userLinkedToClientAttr,
        array $authenticatedUserAttr,
        array $expectedResult
    ): void {
        // Insert authenticated user and user linked to resource with given attributes containing the user role
        $authenticatedUserRow = $this->insertFixturesWithAttributes($authenticatedUserAttr, UserFixture::class);
        if ($authenticatedUserAttr === $userLinkedToClientAttr) {
            $userLinkedToClientRow = $authenticatedUserRow;
        }else{
            // If authenticated user and owner user is not the same, insert owner
            $userLinkedToClientRow = $this->insertFixturesWithAttributes($userLinkedToClientAttr, UserFixture::class);
        }

        // Client status is not authorization relevant for client creation
        $clientStatusId = $this->insertFixturesWithAttributes([], ClientStatusFixture::class)['id'];

        $clientCreationValues = [
            'first_name' => 'New',
            'last_name' => 'Client',
            'birthdate' => '2000-03-15',
            'location' => 'Basel',
            'phone' => '+41 77 222 22 22',
            'email' => 'new-user@email.com',
            'sex' => 'M',
            'user_id' => $userLinkedToClientRow['id'],
            'client_status_id' => $clientStatusId,
        ];

        // Simulate session
        $this->container->get(SessionInterface::class)->set('user_id', $authenticatedUserRow['id']);
        // Make request
        $request = $this->createJsonRequest(
            'POST',
            $this->urlFor('client-submit-create'),
            $clientCreationValues
        );
        $response = $this->app->handle($request);
        // Assert response status code: 201 Created
        self::assertSame($expectedResult[StatusCodeInterface::class], $response->getStatusCode());

        // If db record is expected to be created assert that
        if ($expectedResult['db_entry_created'] === true) {
            $clientDbRow = $this->findLastInsertedTableRow('client');
            // Assert that db entry corresponds to the given client creation values. This is possible as the keys
            // that the frontend sends to the server are the same as database columns.
            // It is done with the function assertTableRow even though we already have the clientDbRow for simplicity
            $this->assertTableRowEquals($clientCreationValues, 'client', $clientDbRow['id']);
            // The same check could also be done with array_intersect_key (which removes any keys from the db array
            // that are not present in the creation values array) like this
            // self::assertSame($clientCreationValues, array_intersect_key($clientDbRow, $clientCreationValues));
        } else {
            // 0 rows expected in client table
            $this->assertTableRowCount(0, 'client');
        }

        $this->assertJsonData($expectedResult['json_response'], $response);
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
        // Insert managing advisor user which is allowed to create clients
        $userRow = $this->insertFixturesWithAttributes(['user_role_id' => 2], UserFixture::class);

        $clientStatusRow = $this->insertFixturesWithAttributes([], ClientStatusFixture::class);

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
    public function testClientSubmitCreateAction_unauthenticated(): void
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
        $userData = $this->insertFixturesWithAttributes([], UserFixture::class);

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
