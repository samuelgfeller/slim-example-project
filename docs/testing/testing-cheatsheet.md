# Testing cheatsheet

## Start testing 
I strongly recommend (also to my future self) to write a bullet point list of exactly what should be tested for each page 
and approximate values of what is expected for each point. This is the only way to have a global overview and not forget 
cases. Also, it adds a lot of clarity and provides precious documentation when having to refactor things.  
It can be quite hard to think about everything in advance though so instead of putting a lot of effort in trying to 
think of all possible cases, I would write down the ones that come to mind and then while implementing more cases 
will come to mind naturally and the list can be extended.

### What to test
This is a big question I have no answer to yet. For me the following useful tests come to mind:
* Page actions.
  * Authenticated page load
    * With user role with the "lowest" rights but as owner.  
    Expected: authenticated user should be able to see the page, so status code 200.
    * Ideally with every different user role where logged-in user is not the owner.  
    Expected result may depend on each role. Often multiple roles have the same "result". If every role can see the
    same thing I would not write different test cases. 
  * Unauthenticated page load
    Expected: redirect to login page with correct query parameters to redirect back to previous page.
* Ajax resource loading (sub resource loaded via Ajax like notes loaded on the client read page)
  * Authenticated load
    * *Sub resource data load is most often covered by the authenticated page load test so not necessary.*
    * Load sub resource with different roles may be interesting if items returned in response body differ 
    depending on the role of the logged-in user such as `userMutationRights` for instance.
      * Load with every different type of user role. Ideally and if well maintained, only the roles where there are changes 
      can be tested, but I think it would make sense to test each role each time per default.
    * Test that deleted resource is NOT in response.
  * Unauthenticated load  
    Expected: Correct status code (401) and login url in response body with correct query parameters that include url to the previous page.
* Ajax resource creation / modification / deletion
  * Authenticated creation / modification / deletion submission
    * User rights: creation / modification / deletion submit with each different user role as authenticated user would cover all cases.
      1. As resource owner (main resource when creation) - non admin
      2. As admin - non resource owner
      3. As non resource owner - non admin
      4. As any other user role that has a different expected behaviour and is relevant to test
    * Validation: as authorized user but invalid form submission (does not apply for deletion)
      1. With every different kind of possible validation error for each field 
      2. 400 malformed body requests where keys are missing or wrongly named  
  * Unauthenticated creation submission
    Expected: Correct status code (401 Unauthorized) and login url in response body with correct query parameters that 
    include url to the previous page.

## Test traits
The needed [test traits](https://github.com/selective-php/test-traits) can be added right after the test class opening
brackets. Here are some of them:
```php
class ClientReadActionTest extends TestCase
{
    use AppTestTrait; // Custom
    use HttpTestTrait;
    use HttpJsonTestTrait;
    use RouteTestTrait;
    use DatabaseTestTrait;
    use FixtureTrait; // Custom
    // ...
}
```
More on it and the whole testing setup can be found in 
[testing.md](https://github.com/samuelgfeller/slim-example-project/blob/master/docs/testing/testing.md)
this is intended to be a cheatsheet in a working environment.

## Page actions
Integration testing page actions is quite limited if the server renders the template serverside as the request body
only contains the rendered page as string, and we don't have access to the variables.   

What we can test however is that the page loads with an expected status code and that if the user is not logged-in,
he is redirected to the login page. 

### Authenticated page action test
Here is the code. Fixtures are explained below.
```php
public function testClientReadPageAction_loggedIn(): void
{
    // Add needed database values to correctly display the page 
    // Insert user linked to client and user that is logged in
    $this->insertFixture('user', (new UserFixture())->records[0]);
    // Insert linked status
    $this->insertFixture('client_status', (new ClientStatusFixture())->records[0]);
    // Insert client that should be displayed
    $this->insertFixture('client', (new ClientFixture())->records[0]);
    $request = $this->createRequest('GET', $this->urlFor('client-read-page', ['client_id' => 1]));
    // Simulate logged-in user with logged-in user id
    $this->container->get(SessionInterface::class)->set('user_id', 1);
    $response = $this->app->handle($request);
    // Assert 200 OK
    self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
}
```

### Non-authenticated page action test
This test is also useful to test if the automatic redirect in the `UserAuthenticationMiddleware` is correct. 
```php
public function testClientReadPageAction_notLoggedIn(): void
{
    // Request route to client read page while not being logged in
    $requestRoute = $this->urlFor('client-read-page', ['client_id' => 1]);
    $request = $this->createRequest('GET', $requestRoute);
    $response = $this->app->handle($request);
    // Assert 302 Found redirect to login url
    self::assertSame(StatusCodeInterface::STATUS_FOUND, $response->getStatusCode());
    // Build expected login url with redirect to initial request route as UserAuthenticationMiddleware.php does
    $expectedLoginUrl = $this->urlFor('login-page', [], ['redirect' => $requestRoute]);
    self::assertSame($expectedLoginUrl, $response->getHeaderLine('Location'));
}
```

## JSON requests
Now with JSON requests we can test a lot more things. I like to render the templates minimally on the server and 
prefer to load linked contents via an AJAX JSON request. This allows for a faster page load and with good content
placeholders it's nice for the users as well.

### Fixtures utility
At first, the needed test data has to be inserted into the database. This is made by the awesome `DatabaseTestTrait.php`.  
To be able to test more agilely with fixtures, I created the `FixtureTrait.php`:
```php
use FixtureTrait;
```

To insert only the records that are linked to the main resource we want to test (or matching another specific criteria),
the function `insertFixtureWhere` can be used like follows:
```php
// Insert one client and needed dependencies
$clientRow = (new ClientFixture())->records[0];
// In array first to assert user data later
$userRow = $this->findRecordsFromFixtureWhere(['id' => $clientRow['user_id']], UserFixture::class);
$this->insertFixture('user', $userRow);
$this->insertFixtureWhere(['id' => $clientRow['client_status_id']], ClientStatusFixture::class);
$this->insertFixture('client', $clientRow);

// HERE - Insert only linked notes
$this->insertFixtureWhere(['client_id' => $clientRow['id']], NoteFixture::class);
```
The dependencies for each fixture are in the fixture class php doc block (and the records foreign keys can be checked).

## Create request
For a json request and assertions later, the `HttpJsonTestTrait.php` is used.
Request is created as follows:
```php
$request = $this->createJsonRequest('GET', $this->urlFor('note-list'))->withQueryParams(['client_id' => 1]);
```
Very important note here, query params can be added directly in the `urlFor()` method, like
`$this->createJsonRequest('GET', $this->urlFor('note-list', [], ['client_id' => 1]))` but 
**[that won't work](https://github.com/Nyholm/psr7/issues/181) if `nyholm/psr7`
is used in the project.** So we have to explicitly use `->withQueryParams(['client_id' => 1])` like shown above.

## Assert Json response

### Resource loading
If we want to test the notes attached to a client that are loaded via Ajax after a client-read request, I would firstly
get all relevant note rows like follows:
```php
$noteRows = $this->findRecordsFromFixtureWhere(['is_main' => 0, 'client_id' => $clientRow['user_id']], NoteFixture::class);
```

Then, as we have multiple notes, init the `$expectedResponseArray` and loop over the resource under test `$noteRows`.  
Note the attached user is also retrieved with `findRecordsFromFixtureWhere()`.
```php
$expectedResponseArray = [];

foreach ($noteRows as $noteRow) {
    // Get linked user record
    $userRow = $this->findRecordsFromFixtureWhere(['id' => $noteRow['user_id']], UserFixture::class)[0];
    $expectedResponseArray[] = [
        // camelCase according to Google recommendation https://stackoverflow.com/a/19287394/9013718
        'noteId' => $noteRow['id'],
        'noteMessage' => $noteRow['message'],
        'noteUpdatedAt' => $noteRow['updated_at'],
        'noteCreatedAt' => $noteRow['created_at'],
        'userFullName' => $userRow['first_name'] . ' ' . $userRow['surname'],
        'userId' => $noteRow['user_id'],
        'userRole' => $userRow['role'],
    ];
}
// Assert response data
$this->assertJsonData($expectedResponseArray, $response);
```
At this point, additionally asserting the database content is pointless as the response body comes from the database.

[//]: # (The paragraph [Assert user rights]&#40;Assert-user-rights&#41; might be relevant if `userMutationRights` is part of the response.)
#### Assert mutation rights 
See test on notes loaded by client_id on how I do it now, but currently I don't know the best way to implement this 
dynamically.

```php
// Get logged-in user row to test user rights
$loggedInUserRow = $this->findRecordsFromFixtureWhere(['id' => $loggedInUserId], UserFixture::class)[0];

// Determine which mutation rights user has
$hasMutationRight = static function (string $role, int $ownerId) use ($loggedInUserId): string {
    // Basically same as js function userHasMutationRights() in client-read-template-note.html.js
    // Has to match user rights rules in NoteUserRightSetter.php
    return $role === 'admin' || $loggedInUserId === $ownerId
        ? MutationRight::ALL->value : MutationRight::NONE->value;
};
foreach ($noteRows as $noteRow) {
    $expectedResponseArray[] = [
        // ...
        'userMutationRights' => $hasMutationRight($loggedInUserRow['role'], $noteRow['user_id']),
    ];
}
```

### Test resource manipulation with different user roles
To test the behaviour of all relevant different user roles I use a data provider that returns the user infos for 
the logged-in user, resource user and expected result so that the same function can be used for all roles.
<details>
  <summary>Show data provider content</summary>

`tests/Provider/Client/ClientReadCaseProvider.php`
```php
public function provideAuthenticatedAndLinkedUserForNote(): array
{
    $userData = $this->findRecordsFromFixtureWhere(['role' => 'user'], UserFixture::class)[0];
    return [
        [ // ? Authenticated user is resource owner - non admin
            // User to whom the note is linked
            'owner_user' => $userData,
            'authenticated_user' => $userData,
            'expected_result' => [
                'creation' => [
                    StatusCodeInterface::class => StatusCodeInterface::STATUS_CREATED,
                ],
                'modification' => [
                    // All users may edit the main note but only change their own so there are different expected results
                    'main_note' => [
                        // For a PUT request: HTTP 200, HTTP 204 should imply "resource updated successfully"
                        // https://stackoverflow.com/a/2342589/9013718
                        StatusCodeInterface::class => StatusCodeInterface::STATUS_OK,
                        'json_response' => [
                            'status' => 'success',
                            'data' => null,
                        ],
                    ],
                    'normal_note' => [
                        StatusCodeInterface::class => StatusCodeInterface::STATUS_OK,
                        'json_response' => [
                            'status' => 'success',
                            'data' => null,
                        ],
                        // Is db supposed to change
                        'db_changed' => true,
                    ],
                ],
                'deletion' => [
                    // Delete main note request is expected to produce 405 HttpMethodNotAllowedException
                    'normal_note' => [
                        // For a DELETE request: HTTP 200 or HTTP 204 should imply "resource deleted successfully"
                        StatusCodeInterface::class => StatusCodeInterface::STATUS_OK,
                        'json_response' => [
                            'status' => 'success',
                        ],
                        // Is db supposed to change
                        'db_changed' => true,
                    ],
                ],
            ],
        ],
        [ // ? Authenticated user is admin - non resource owner
            'owner_user' => $userData,
            'authenticated_user' => $this->findRecordsFromFixtureWhere(['role' => 'admin'], UserFixture::class)[0],
            'expected_result' => [
                'creation' => [
                    StatusCodeInterface::class => StatusCodeInterface::STATUS_CREATED,
                ],
                'modification' => [
                    'main_note' => [
                        StatusCodeInterface::class => StatusCodeInterface::STATUS_OK,
                        'json_response' => [
                            'status' => 'success',
                            'data' => null,
                        ],
                    ],
                    'normal_note' => [
                        StatusCodeInterface::class => StatusCodeInterface::STATUS_OK,
                        'json_response' => [
                            'status' => 'success',
                            'data' => null,
                        ],
                        'db_changed' => true,
                    ],
                ],
                'deletion' => [
                    // Delete main note request is expected to produce 405 HttpMethodNotAllowedException
                    'normal_note' => [
                        // For a DELETE request: HTTP 200 or HTTP 204 should imply "resource deleted successfully"
                        StatusCodeInterface::class => StatusCodeInterface::STATUS_OK,
                        'json_response' => [
                            'status' => 'success',
                        ],
                        // Is db supposed to change
                        'db_changed' => true,
                    ],
                ],
            ],
        ],
        [ // ? Authenticated user is not the resource owner and not admin
            'owner_user' => $userData,
            // Get user with role user that is not the same then $userData
            'authenticated_user' => $this->findRecordsFromFixtureWhere(
                ['role' => 'user'],
                UserFixture::class,
                ['id' => $userData['id']]
            )[0],
            'expected_result' => [
                'creation' => [
                    // Should be created as users that are not linked to client are able to create notes - this will be diff
                    StatusCodeInterface::class => StatusCodeInterface::STATUS_CREATED,
                ],
                'modification' => [
                    // All users may edit the main note but only change their own so there are different expected results
                    'main_note' => [
                        StatusCodeInterface::class => StatusCodeInterface::STATUS_OK,
                        'json_response' => [
                            'status' => 'success',
                            'data' => null,
                        ],
                    ],
                    'normal_note' => [
                        StatusCodeInterface::class => StatusCodeInterface::STATUS_FORBIDDEN,
                        'json_response' => [
                            'status' => 'error',
                            'message' => 'You can only edit your own note or need to be an admin to edit others'
                        ],
                        'db_changed' => false,
                    ],
                ],
                'deletion' => [
                    // Delete main note request is expected to produce 405 HttpMethodNotAllowedException
                    'normal_note' => [
                        // For a DELETE request: HTTP 200 or HTTP 204 should imply "resource deleted successfully"
                        StatusCodeInterface::class => StatusCodeInterface::STATUS_FORBIDDEN,
                        'json_response' => [
                            'status' => 'error',
                            'message' => 'You have to be note author to delete this note.',
                        ],
                        // Is db supposed to change
                        'db_changed' => false,
                    ],
                ],
            ],
        ],
    ];
}
```
Modification and deletion examples are at the bottom. 
</details>

Here is a typical fixtures' insertion, POST request with authentication and HTTP status code assertion for 
resource creation with different user roles.

`tests/Integration/Client/ClientReadActionTest.php`
```php
/**
 * Test note creation on client-read page while being authenticated 
 * with different user roles.
 *
 * @dataProvider \App\Test\Provider\Client\ClientReadCaseProvider::provideAuthenticatedAndLinkedUserForNote()
 * @return void
 */
public function testClientReadNoteCreation(array $userLinkedToClientData, array $authenticatedUserData, array $expectedResult): void
{
    $this->insertFixture('user', $userLinkedToClientData);
    // If authenticated user and user that should be linked to client is different, insert authenticated user
    if ($userLinkedToClientData['id'] !== $authenticatedUserData['id']) {
        $this->insertFixture('user', $authenticatedUserData);
    }
    // Insert one client linked to this user
    $clientRow = $this->findRecordsFromFixtureWhere(['user_id' => $userLinkedToClientData['id']],
        ClientFixture::class)[0];
    // In array first to assert user data later
    $this->insertFixtureWhere(['id' => $clientRow['client_status_id']], ClientStatusFixture::class);
    $this->insertFixture('client', $clientRow);
    // Create request
    $noteMessage = 'Test note';
    $request = $this->createJsonRequest(
        'POST',
        $this->urlFor('note-submit-creation'),
        [
            'message' => $noteMessage,
            'client_id' => $clientRow['id'],
            'is_main' => 0
        ]
    );
    // Simulate logged-in user
    $this->container->get(SessionInterface::class)->set('user_id', $authenticatedUserData['id']);
    // Make request
    $response = $this->app->handle($request);
    // Assert 201 Created redirect to login url
    self::assertSame($expectedResult['creation'][StatusCodeInterface::class], $response->getStatusCode());
    // Asserting database is below ...
}
```
The database has to be asserted before the response content if it contains values from the database.
#### Assert database
To be able to use extended select functions, `use DatabaseExtensionTestTrait;` has to be added after `DatabaseTestTrait`.
```php
// Assert database 
// Find freshly inserted note
$noteDbRow = $this->findLastInsertedTableRow('note');
// Assert the row column values
$this->assertTableRow(['message' => $noteMessage, 'is_main' => 0], 'note', (int)$noteDbRow['id']);
```
#### Assert response body
```php
// Assert response
$expectedResponseJson = [
    'status' => 'success',
    'data' => [
        'userFullName' => $nonAdminUserRow['first_name'] . ' ' . $nonAdminUserRow['surname'],
        'noteId' => $noteDbRow['id'],
        'createdDateFormatted' => $this->dateTimeToClientReadNoteFormat($noteDbRow['created_at']),
    ],
];
$this->assertJsonData($expectedResponseJson, $response);
```


### Assert JSON response when unauthenticated
When protected AJAX request is sent to the server and user is not logged-in, the client should redirect the user to 
the login form. The redirect action [cannot be initiated by the server](https://github.com/odan/slim4-tutorial/issues/44), 
so it has to be done by the client.
This is the simplified code inside the default Ajax handleFail() method:
```js
// Not logged in, redirect to login url
if (xhr.status === 401) {
    window.location.href = JSON.parse(xhr.responseText).loginUrl;
}
```
Not only should the client be redirected to the login page, but also redirected back to the where he/she was before having
to log in again. To make this possible, the server has to respond with the full login url with redirect back query params.   
This is done by the `AuthenticationMiddleware` with the help of one of two custom HTTP headers 
`Redirect-to-url-if-unauthorized` or `Redirect-to-route-name-if-unauthorized`.

#### Assert response with `Redirect-to-url-if-unauthorized`
```js
// Content-type has to be json
xHttp.setRequestHeader("Redirect-to-url-if-unauthorized", basePath + "client/" + clientId);
```
```php
$request = $this->createJsonRequest('GET', $this->urlFor('note-list'))
    ->withQueryParams(['client_id' => 1]);
// Create url where client should be redirected to after login    
$redirectToUrlAfterLogin = $this->urlFor('client-read-page', ['client_id' => 1]);
$request = $request->withAddedHeader('Redirect-to-url-if-unauthorized', $redirectToUrlAfterLogin);
// Make request
$response = $this->app->handle($request);
// Assert response HTTP status code: 401 Unauthorized
self::assertSame(StatusCodeInterface::STATUS_UNAUTHORIZED, $response->getStatusCode());
// Build expected login url as UserAuthenticationMiddleware.php does
$expectedLoginUrl = $this->urlFor('login-page', [], ['redirect' => $redirectToUrlAfterLogin]);
// Assert that response contains correct login url
$this->assertJsonData(['loginUrl' => $expectedLoginUrl], $response);
```
<details>
  <summary>Assert response with `Redirect-to-route-name-if-unauthorized`</summary>

```php
$request = $this->createJsonRequest('GET', $this->urlFor('ajax-route-name'))
    ->withQueryParams(['client_id' => 1]);
// Provide redirect to if unauthorized header to test if UserAuthenticationMiddleware returns correct login url
$redirectAfterLoginRouteName = 'page-route-name';
$request = $request->withAddedHeader('Redirect-to-route-name-if-unauthorized', 'page-route-name');
// Make request
$response = $this->app->handle($request);
// Assert response HTTP status code: 401 Unauthorized
self::assertSame(StatusCodeInterface::STATUS_UNAUTHORIZED, $response->getStatusCode());
// Build expected login url as UserAuthenticationMiddleware.php does
$expectedLoginUrl = $this->urlFor('login-page', [], ['redirect' => $this->urlFor($redirectAfterLoginRouteName)]);
// Assert that response contains correct login url
$this->assertJsonData(['loginUrl' => $expectedLoginUrl], $response);
```
</details>

#### Assert response without custom header
This is basically the same as [non-authenticated page action test](#non-authenticated-page-action-test).

### Test validation and assert errors 
Form fields generally have specific criteria that are validated.
#### Generate Validation cases with a case provider
To be able to test different invalid inputs in one test, the different cases are provided via data provider.  
`tests/Provider/Client/ClientReadCaseProvider.php`
```php
/**
 * Returns combinations of invalid data to trigger validation exception.
 *
 * @return array
 */
public function provideInvalidNoteAndExpectedResponseData(): array
{
    // Message over 500 chars
    $tooLongMsg = 'iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii
iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii
iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii
iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii
iiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii';

    return [
        [
            'message_too_short' => 'Me',
            'json_response' => [
                'status' => 'error',
                'message' => 'Validation error',
                'data' => [
                    'message' => 'There is something in the note data that couldn\'t be validated',
                    'errors' => [
                        0 => [
                            'field' => 'message',
                            'message' => 'Required minimum length is 4',
                        ]
                    ]
                ]
            ]
        ],
        [
            'message_too_long' => $tooLongMsg,
            'json_response' => [
                'status' => 'error',
                'message' => 'Validation error',
                'data' => [
                    'message' => 'There is something in the note data that couldn\'t be validated',
                    'errors' => [
                        0 => [
                            'field' => 'message',
                            'message' => 'Required maximum length is 500',
                        ]
                    ]
                ]
            ]
        ],

    ];
}
```
#### Test validation on resource modification
This is the full test where a note is edited with invalid inputs given by the data provider above:
`tests/Integration/Client/ClientReadActionTest.php`
```php
/**
 * Test note modification on client-read page with invalid data.
 * Fixture dependencies:
 *   - 1 client
 *   - 1 user linked to client
 *   - 1 note that is linked to the client and the user
 *
 * @dataProvider \App\Test\Provider\Client\ClientReadCaseProvider::provideInvalidNoteAndExpectedResponseData()
 * @return void
 */
public function testClientReadNoteModification_invalid(string $invalidMessage, array $expectedResponseData): void
{
    // Add the minimal needed data
    $clientData = (new ClientFixture())->records[0];
    // Insert user linked to client and user that is logged in
    $userData = $this->findRecordsFromFixtureWhere(['id' => $clientData['user_id']], UserFixture::class)[0];
    $this->insertFixture('user', $userData);
    // Insert linked status
    $this->insertFixtureWhere(['id' => $clientData['client_status_id']], ClientStatusFixture::class);
    // Insert client
    $this->insertFixture('client', $clientData);
    // Insert note linked to client and user
    $noteData = $this->findRecordsFromFixtureWhere(['client_id' => $clientData['id'], 'user_id' => $userData['id']]
        NoteFixture::class)[0];
    $this->insertFixture('note', $noteData);
    // Simulate logged-in user with same user as linked to client
    $this->container->get(SessionInterface::class)->set('user_id', $userData['id']);
    $request = $this->createJsonRequest(
        'PUT', $this->urlFor('note-submit-modification', ['note_id' => $noteData['id']]),
        ['message' => $invalidMessage]
    );
    $response = $this->app->handle($request);
    // Assert 422 Unprocessable entity
    self::assertSame(StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    // Assert json response data
    $this->assertJsonData($expectedResponseData, $response);
}
```

#### Test malformed request body
When the client makes a request and the body has not the right syntax (e.g. wrong key or invalid amount of keys). 
Here as well in order to not having to write multiple tests, I'm using a data provider:

`tests/Provider/Client/ClientReadCaseProvider.php`
```php
/**
 * Provide malformed note message request body
 * 
 * @return array
 */
public function provideMalformedNoteRequestBody(): array
{
    return [
        [
            'wrong_key' => [
                'wrong_message_key' => 'Message',
            ],
        ],
        [
            'wrong_amount' => [
                'message' => 'Message',
                'second_key' => 'invalid',
            ],
        ]
    ];
}
```
And the actual test function:
`tests/Integration/Client/ClientReadActionTest.php`
```php
/**
 * Test client read note modification with malformed request body
 *
 * @dataProvider \App\Test\Provider\Client\ClientReadCaseProvider::provideMalformedNoteRequestBody()
 * @return void
 */
public function testClientReadNoteModification_malformedRequest(array $malformedRequestBody): void
{
    // Action class should directly return error so only logged-in user has to be inserted
    $userData = (new UserFixture())->records[0];
    $this->insertFixture('user', $userData);
    // Simulate logged-in user with same user as linked to client
    $this->container->get(SessionInterface::class)->set('user_id', $userData['id']);
    $request = $this->createJsonRequest(
        'PUT', $this->urlFor('note-submit-modification', ['note_id' => 1]),
        $malformedRequestBody
    );
    // Bad Request (400) means that the client sent the request wrongly; it's a client error
    $this->expectException(HttpBadRequestException::class);
    $this->expectExceptionMessage('Request body malformed.');
    // Handle request after defining expected exceptions
    $this->app->handle($request);
}
```
  

-----

## More examples
<details>
  <summary><b>Client read normal and main note modification with different user roles</b></summary>

`tests/Integration/Client/ClientReadActionTest.php`
```php
/**
 * Test note modification on client-read page while being authenticated.
 * Fixture dependencies:
 *   - 1 client that is linked to the non admin user retrieved in the provider
 *   - 1 main note that is linked to the same non admin user and to the client
 *   - 1 normal note that is linked to the same user and client
 *   - 1 normal note that is not linked to this user but the client
 *
 * @dataProvider \App\Test\Provider\Client\ClientReadCaseProvider::provideAuthenticatedAndLinkedUserForNote()
 * @return void
 */
public function testClientReadNoteModification(
    array $userLinkedToNoteData,
    array $authenticatedUserData,
    array $expectedResult
): void {
    $this->insertFixture('user', $userLinkedToNoteData);
    // If authenticated user and user that should be linked to client is different, insert authenticated user
    if ($userLinkedToNoteData['id'] !== $authenticatedUserData['id']) {
        $this->insertFixture('user', $authenticatedUserData);
    }

    // Insert one client linked to this user
    $clientRow = $this->findRecordsFromFixtureWhere(['user_id' => $userLinkedToNoteData['id']],
        ClientFixture::class)[0];
    // In array first to assert user data later
    $this->insertFixtureWhere(['id' => $clientRow['client_status_id']], ClientStatusFixture::class);
    $this->insertFixture('client', $clientRow);

    // Insert main note attached to client and given "owner" user
    $mainNoteData = $this->findRecordsFromFixtureWhere(
        ['is_main' => 1, 'user_id' => $userLinkedToNoteData['id'], 'client_id' => $clientRow['id']],
        NoteFixture::class
    )[0];
    $this->insertFixture('note', $mainNoteData);
    // Insert normal note attached to client and given "owner" user
    $normalNoteData = $this->findRecordsFromFixtureWhere(
        ['is_main' => 0, 'user_id' => $userLinkedToNoteData['id'], 'client_id' => $clientRow['id']],
        NoteFixture::class
    )[0];
    $this->insertFixture('note', $normalNoteData);

    // Simulate logged-in user
    $this->container->get(SessionInterface::class)->set('user_id', $authenticatedUserData['id']);

    $newNoteMessage = 'New note message';
    // --- *MAIN note request ---
    // Create request to edit main note
    $mainNoteRequest = $this->createJsonRequest(
        'PUT', $this->urlFor('note-submit-modification', ['note_id' => $mainNoteData['id']]),
        ['message' => $newNoteMessage,]
    );
    // Make request
    $mainNoteResponse = $this->app->handle($mainNoteRequest);

    // Assert 200 OK note updated successfully
    self::assertSame(
        $expectedResult['modification']['main_note'][StatusCodeInterface::class],
        $mainNoteResponse->getStatusCode()
    );

    // Database is always expected to change for the main note as every user can change it
    $this->assertTableRow(['message' => $newNoteMessage], 'note', $mainNoteData['id']);

    // Assert response
    $this->assertJsonData($expectedResult['modification']['main_note']['json_response'], $mainNoteResponse);

    // --- *NORMAL NOTE REQUEST ---
    $normalNoteRequest = $this->createJsonRequest(
        'PUT', $this->urlFor('note-submit-modification', ['note_id' => $normalNoteData['id']]),
        ['message' => $newNoteMessage,]
    );
    // Make request
    $normalNoteResponse = $this->app->handle($normalNoteRequest);
    self::assertSame(
        $expectedResult['modification']['normal_note'][StatusCodeInterface::class],
        $normalNoteResponse->getStatusCode()
    );

    // If db is expected to change assert the new message
    if ($expectedResult['modification']['normal_note']['db_changed'] === true) {
        $this->assertTableRow(['message' => $newNoteMessage], 'note', $normalNoteData['id']);
    } else {
        // If db is not expected to change message should remain the same as when it was inserted first
        $this->assertTableRow(['message' => $normalNoteData['message']], 'note', $normalNoteData['id']);
    }

    $this->assertJsonData($expectedResult['modification']['normal_note']['json_response'], $normalNoteResponse);
}
```
</details>

<details>
  <summary><b>Client read normal and main note deletion with different user roles</b></summary>

`tests/Integration/Client/ClientReadActionTest.php`
```php
/**
 * Test normal and main note deletion on client-read page
 * while being authenticated.
 *
 * @dataProvider \App\Test\Provider\Client\ClientReadCaseProvider::provideAuthenticatedAndLinkedUserForNote()
 * @return void
 */
public function testClientReadNoteDeletion(
    array $userLinkedToNoteData,
    array $authenticatedUserData,
    array $expectedResult
): void {
    $this->insertFixture('user', $userLinkedToNoteData);
    // If authenticated user and user that is linked to client is different, insert authenticated user
    if ($userLinkedToNoteData['id'] !== $authenticatedUserData['id']) {
        $this->insertFixture('user', $authenticatedUserData);
    }
    // Insert one client linked to this user
    $clientRow = $this->findRecordsFromFixtureWhere(['user_id' => $userLinkedToNoteData['id']],
        ClientFixture::class)[0];
    // In array first to assert user data later
    $this->insertFixtureWhere(['id' => $clientRow['client_status_id']], ClientStatusFixture::class);
    $this->insertFixture('client', $clientRow);
    // Insert main note attached to client and given "owner" user
    $mainNoteData = $this->findRecordsFromFixtureWhere(
        [
            'is_main' => 1,
            'user_id' => $userLinkedToNoteData['id'],
            'client_id' => $clientRow['id'],
            'deleted_at' => null
        ],
        NoteFixture::class
    )[0];
    $this->insertFixture('note', $mainNoteData);
    // Insert normal note attached to client and given "owner" user
    $normalNoteData = $this->findRecordsFromFixtureWhere(
        [
            'is_main' => 0,
            'user_id' => $userLinkedToNoteData['id'],
            'client_id' => $clientRow['id'],
            'deleted_at' => null
        ],
        NoteFixture::class
    )[0];
    $this->insertFixture('note', $normalNoteData);
    // Simulate logged-in user
    $this->container->get(SessionInterface::class)->set('user_id', $authenticatedUserData['id']);
    // --- *MAIN note request ---
    // Create request to edit main note
    $mainNoteRequest = $this->createJsonRequest(
        'DELETE',
        $this->urlFor('note-submit-delete', ['note_id' => $mainNoteData['id']]),
    );
    // As deleting the main note is not a valid request the server throws an HttpMethodNotAllowed exception
    $this->expectException(HttpMethodNotAllowedException::class);
    $this->expectExceptionMessage('The main note cannot be deleted.');
    // Make request
    $this->app->handle($mainNoteRequest);
    // Database is not expected to change for the main note as there is no way to delete it from the frontend
    $this->assertTableRow(['deleted_at' => null], 'note', $mainNoteData['id']);
    // --- *NORMAL NOTE REQUEST ---
    $normalNoteRequest = $this->createJsonRequest(
        'DELETE',
        $this->urlFor('note-submit-delete', ['note_id' => $normalNoteData['id']]),
    );
    // Make request
    $normalNoteResponse = $this->app->handle($normalNoteRequest);
    self::assertSame(
        $expectedResult['deletion']['normal_note'][StatusCodeInterface::class],
        $normalNoteResponse->getStatusCode()
    );
    // Assert database
    $noteDeletedAtValue = $this->findTableRowById('note', $normalNoteData['id'])['deleted_at'];
    // If db is expected to change assert the new message (when provided authenticated user is allowed to do action)
    if ($expectedResult['deletion']['normal_note']['db_changed'] === true) {
        // Test that deleted at is not null
        self::assertNotNull($noteDeletedAtValue);
    } else {
        // If db is not expected to change message should remain the same as when it was inserted first
        self::assertNull($noteDeletedAtValue);
    }
    $this->assertJsonData($expectedResult['deletion']['normal_note']['json_response'], $normalNoteResponse);
}
```
</details>