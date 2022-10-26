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
* Page actions
  * Authenticated page load
    * With user role with the "lowest" rights but as owner  
    Expected: authenticated user should be able to see the page, so status code 200
    * Ideally with every different user role where logged-in user is not the owner   
    Expected result may depend on each role. Often multiple roles have the same "result". If every role can see the
    same thing I would not write different test cases
  * Unauthenticated page load
    Expected: redirect to login page with correct query parameters to redirect back to previous page
* Ajax resource loading (sub resource loaded via Ajax like notes loaded on the client read page)
  * Authenticated load
    * *Sub resource data load is most often covered by the authenticated page load test so not necessary.*
    * Load sub resource with different roles may be interesting if items returned in response body differ 
    depending on the role of the logged-in user (asserting `$privilege` for instance)
      * Load with every different type of user role. Ideally and if well maintained, only the roles where there are 
        relevant changes to be tested [example](#provider-for-note-list-action-test)
    * Test that deleted resource is NOT in response
  * Unauthenticated load  
    Expected: Correct status code (401) and login url in response body with correct query parameters that include url to the previous page
* Ajax resource creation / modification / deletion
  * Authenticated creation / modification / deletion submission
    * User rights: creation / modification / deletion submit with each different user role as authenticated user
      1. Each role as resource owner (main resource owner for creation) - *e.g. role "newcomer" and owner, "advisor" and owner etc.*
      2. Each role NOT as owner - *e.g. role "newcomer" and not owner, "advisor" and not owner etc.*
         * Not every role is needed as roles work in a hierarchical way. It doesn't have to be tested further than the lowest 
         privilege that is allowed to perform action when not owner. *e.g. "admin" can do at least everything "managing_advisor" can do*
      3. Any other user role that has a different expected behaviour and is relevant to test
    * Validation: as authorized user but invalid form submission (does not apply for deletion)
      1. With every different kind of possible validation error for each field 
      2. 400 malformed body requests where keys are missing or wrongly named  
  * Unauthenticated creation submission
    Expected: Correct status code (401 Unauthorized) and login url in response body with correct query parameters that 
    include url to the previous page

## Page actions
Integration testing page actions is quite limited if the server renders the template serverside as the request body
only contains the rendered page as string, and we don't have access to the variables.   

What we can test however is that the page loads with an expected status code and that if the user is not logged-in,
he is redirected to the login page. 

### Authenticated page action test
The needed [test traits](https://github.com/selective-php/test-traits) can be added right after the test class opening
brackets. More on it and the whole testing setup can be found in 
[testing.md](https://github.com/samuelgfeller/slim-example-project/blob/master/docs/testing/testing.md)
this is intended to be a cheatsheet in a working environment. Fixture use is explained below.
```php
use AppTestTrait; // Custom
use HttpTestTrait;
use RouteTestTrait;
use DatabaseTestTrait;
use FixtureTrait; // Custom

public function testClientReadPageAction_authenticated(): void
{
    // Insert linked and authenticated user
    $userId = $this->insertFixturesWithAttributes([], UserFixture::class)['id'];
    // Insert linked client status
    $clientStatusId = $this->insertFixturesWithAttributes([], ClientStatusFixture::class)['id'];
    // Add needed database values to correctly display the page
    $clientRow = $this->insertFixturesWithAttributes(['user_id' => $userId, 'client_status_id' => $clientStatusId],
        ClientFixture::class);
        
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
public function testClientReadPageAction_unauthenticated(): void
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
prefer to load linked contents via an Ajax JSON request. This allows for a faster page load and with good content
placeholders it's nice for the users as well.

### Fixtures utility
At first, the needed test data has to be inserted into the database. This is made by the awesome `DatabaseTestTrait.php`.  
To be able to test more agilely with fixtures, I created the `FixtureTrait.php`:
```php
use FixtureTrait;
```
The main advantage is being in control of the fixtures in the test function. The `$records` values of the `Fixture` 
are not relevant (it just has to be valid and the first `deleted_at` must be `null`). The needed records 
are "shaped" in the test function meaning the relevant attributes are all set during the test. This means that we don't
depend on the `$records` containing right ids or values which would be a nightmare to maintain if 
change are made to the fixture records. Using one pool for a lot of different functions goes against 
[single-responsibility principle](https://en.wikipedia.org/wiki/Single-responsibility_principle). 


#### `insertFixturesWithAttributes()`
Parameters are 
* `$attributes` where an associative array is expected with as key the column and value the value 
we want to attribute to this column. 
* `$fixtureClass` where the class string of a fixture is expected such as `UserFixture::class`.
* `$amount` optional amount of rows you want to generate. Default 1.

Return value is an associative array of the inserted row including the id.

#### Usage example
To insert only the records that matching specific criteria, the function `insertFixturesWithAttributes` could be used like follows:
```php
$clientOwnerId = $this->insertFixturesWithAttributes(['user_role_id' => 3], UserFixture::class)['id'];
// Insert linked status
$clientStatusId = $this->insertFixturesWithAttributes([], ClientStatusFixture::class)['id'];
// Insert client row
$clientRow = $this->insertFixturesWithAttributes(
    ['user_id' => $clientOwnerId, 'client_status_id' => $clientStatusId],
    ClientFixture::class
);
// Insert linked note
$noteRow = $this->insertFixturesWithAttributes(
    ['is_main' => 0, 'client_id' => $clientRow['id'], 'user_id' => $clientOwnerId],
    NoteFixture::class
);
```

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

### Simulate session, execute request and assert status code
```php
// Simulate logged-in user
$this->container->get(SessionInterface::class)->set('user_id', $userRow['id']);
// Handle request
$response = $this->app->handle($request);
// Assert 200 OK
self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
```

## Assert JSON response
The database has to be asserted before the response content if it contains values from the database.
### Assert database
To be able to use extended select functions, `use DatabaseExtensionTestTrait;` has to be added after `DatabaseTestTrait`.
```php
// Assert database 
// Find freshly inserted note
$noteDbRow = $this->findLastInsertedTableRow('note');
// Assert the row column values
$this->assertTableRowEquals(['message' => $noteMessage, 'is_main' => 0], 'note', (int)$noteDbRow['id']);
```
`$this->assertTableRow` strictly asserts database with given array. If the order of keys does not matter, 
I'd use `assertTableRowEquals` to prevent false positive test failures.
### Assert response body
```php
// Assert response
$this->assertJsonData([
    'status' => 'success',
    'data' => [
        'userFullName' => $nonAdminUserRow['first_name'] . ' ' . $nonAdminUserRow['surname'],
        'noteId' => $noteDbRow['id'],
        'createdDateFormatted' => $this->dateTimeToClientReadNoteFormat($noteDbRow['created_at']),
    ],
], $response);
```

### Test and assert CRUD requests
Examples of whole CRUD test functions with different user roles: 
**[Test and assert CRUD requests examples](#test-and-assert-CRUD-requests-examples)**

### Test validation and assert errors
Form fields generally have specific criteria like a minimum length or specific format that are validated.

Test function, provider and assertions: **[Test validation and assert errors example](#test-validation-and-assert-errors-example)**.

### Test and assert malformed request body
When the client makes a request and the body has not the right syntax (e.g. wrong key or invalid amount of keys).

Test function, provider and assertions: **[Test and assert malformed request body example](#test-and-assert-malformed-request-body-example)**.

## Test and assert JSON response when unauthenticated
When protected Ajax request is sent to the server and user is not logged-in, the client should redirect the user to 
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
xHttp.setRequestHeader("Redirect-to-url-if-unauthorized", basePath + "clients/" + clientId);
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
  <summary><h4>Assert response with `Redirect-to-route-name-if-unauthorized`</h4></summary>

```php
$request = $this->createJsonRequest('GET', $this->urlFor('ajax-route-name'))
    ->withQueryParams(['client_id' => 1]);
// Provide redirect to if unauthorized header to test if UserAuthenticationMiddleware returns correct login url
$redirectAfterLoginRouteName = 'page-route-name';
$request = $request->withAddedHeader('Redirect-to-route-name-if-unauthorized', $redirectAfterLoginRouteName);
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

Asserting unauthenticated response without custom header is basically the same as 
[non-authenticated page action test](#non-authenticated-page-action-test).

## Test and assert CRUD requests examples
### Resource loading

<details>
  <summary><h4>List action test example with privilege assertion but without filter</h4></summary>

#### Test note list action
```php
/**
 * Tests notes that are loaded with ajax on client read page.
 *
 * @dataProvider \App\Test\Provider\Note\NoteCaseProvider::provideUserAttributesAndExpectedResultForNoteList()
 * Different privileges of notes depending on authenticated user and
 * note owner are tested with the provider.
 *
 * @param array $userLinkedToNoteAttr note owner attributes containing the user_role_id
 * @param array $authenticatedUserAttr authenticated user attributes containing the user_role_id
 * @param array $expectedResult HTTP status code and privilege
 * @return void
 */
public function testNoteListAction(
    array $userLinkedToNoteAttr,
    array $authenticatedUserAttr,
    array $expectedResult
): void {
    // Insert authenticated user and user linked to resource with given attributes containing the user role
    $authenticatedUserRow = $this->insertFixturesWithAttributes($authenticatedUserAttr, UserFixture::class);
    if ($authenticatedUserAttr === $userLinkedToNoteAttr) {
        $userLinkedToNoteRow = $authenticatedUserRow;
    }else{
        // If authenticated user and owner user is not the same, insert owner
        $userLinkedToNoteRow = $this->insertFixturesWithAttributes($userLinkedToNoteAttr, UserFixture::class);
    }
    // As the client owner is not relevant, another user (advisor) is taken. If this test fails in the future
    // because note read rights change (e.g. that newcomers may not see the notes from everyone), the
    // client owner id has to be added to the provider
    $clientOwnerId = $this->insertFixturesWithAttributes(['user_role_id' => 3], UserFixture::class)['id'];
    // Insert linked status
    $clientStatusId = $this->insertFixturesWithAttributes([], ClientStatusFixture::class)['id'];
    // Insert client row
    $clientRow = $this->insertFixturesWithAttributes(
        ['user_id' => $clientOwnerId, 'client_status_id' => $clientStatusId],
        ClientFixture::class
    );
    // Insert linked note. Only one per test to simplify assertions with different privileges
    $noteData = $this->insertFixturesWithAttributes(
        ['is_main' => 0, 'client_id' => $clientRow['id'], 'user_id' => $userLinkedToNoteRow['id']],
        NoteFixture::class
    );
    // Simulate logged-in user with logged-in user id
    $this->container->get(SessionInterface::class)->set('user_id', $authenticatedUserRow['id']);
    // Make request
    $request = $this->createJsonRequest('GET', $this->urlFor('note-list'))->withQueryParams(['client_id' => 1]);
    $response = $this->app->handle($request);
    // Assert status code
    self::assertSame($expectedResult[StatusCodeInterface::class], $response->getStatusCode());
    $expectedResponseArray[] = [
        // camelCase according to Google recommendation https://stackoverflow.com/a/19287394/9013718
        'noteId' => $noteData['id'],
        'noteMessage' => $noteData['message'],
        // Same format as in NoteFinder:findAllNotesFromClientExceptMain()
        'noteCreatedAt' => (new \DateTime($noteData['created_at']))->format('d. F Y • H:i'),
        'noteUpdatedAt' => (new \DateTime($noteData['updated_at']))->format('d. F Y • H:i'),
        'userId' => $noteData['user_id'],
        'userFullName' => $userLinkedToNoteRow['first_name'] . ' ' . $userLinkedToNoteRow['surname'],
        // Has to match privilege from NoteAuthorizationGetter.php (rules are in NoteAuthorizationChecker.php)
        'privilege' => $expectedResult['privilege']->value,
    ];
    // Assert response data
    $this->assertJsonData($expectedResponseArray, $response);
}
```
#### Provider for note list action test
All different relevant combinations of user roles are provided in the following data provider. There are not 
so much cases because newcomer and advisor owner may only change their own posts and managing_advisor and higher 
are allowed to edit others' notes.
```php
public function provideUserAttributesAndExpectedResultForNoteList(): array
{
    // Get users with the different roles
    $managingAdvisorRow = ['user_role_id' => 2];
    $advisorRow = ['user_role_id' => 3];
    $newcomerRow = ['user_role_id' => 4];
    return [
        [// ? newcomer not owner of note
            'note_owner' => $advisorRow,
            'authenticated_user' => $newcomerRow,
            'expected_result' => [
                StatusCodeInterface::class => StatusCodeInterface::STATUS_OK,
                'privilege' => Privilege::CREATE
            ],
        ],
        [// ? newcomer owner of note
            'note_owner' => $newcomerRow,
            'authenticated_user' => $newcomerRow,
            'expected_result' => [
                StatusCodeInterface::class => StatusCodeInterface::STATUS_OK,
                'privilege' => Privilege::DELETE
            ],
        ],
        // Advisor owner would be the same as newcomer
        [// ? managing advisor not owner of note
            'note_owner' => $advisorRow,
            'authenticated_user' => $managingAdvisorRow,
            'expected_result' => [
                StatusCodeInterface::class => StatusCodeInterface::STATUS_OK,
                // Full privilege, so it must not be tested further
                'privilege' => Privilege::DELETE
            ],
        ],
    ];
}
```

</details>

<details>
<summary><h4>List action test with filter and privilege assertion</h4></summary>
Client list test action

</details>

### Test and assert basic data mutation (create, update and delete actions) with one provider 
To test the behaviour of all relevant different user roles a data provider is used that returns the relevant user attributes 
for the logged-in user, resource owner and expected result so that the same test function can be used for all roles.  

Examples below are on the notes and they have a little added complexity as they can be a "main note" that changes the 
privileges and expected results.  
<details>
  <summary><h4>NoteCaseProvider for create update and delete</h4></summary>

`tests/Provider/Note/NoteCaseProvider.php`
```php
public function provideUserAttributesAndExpectedResultForNoteCUD(): array
{
    // Set different user role attributes
    $managingAdvisorAttributes = ['user_role_id' => 2];
    $advisorAttributes = ['user_role_id' => 3];
    $newcomerAttributes = ['user_role_id' => 4];
    $authorizedResult = [
        // For a DELETE, PUT request: HTTP 200, HTTP 204 should imply "resource updated successfully"
        // https://stackoverflow.com/a/2342589/9013718
        StatusCodeInterface::class => StatusCodeInterface::STATUS_OK,
        // Is db supposed to change
        'db_changed' => true,
        'json_response' => [
            'status' => 'success',
            'data' => null,
        ],
    ];
    $unauthorizedResult = [
        StatusCodeInterface::class => StatusCodeInterface::STATUS_FORBIDDEN,
        'db_changed' => false,
        'json_response' => [
            'status' => 'error',
            'message' => 'Not allowed to change note.',
        ]
    ];
    $unauthorizedUpdateResult = $unauthorizedResult;
    $unauthorizedUpdateResult['json_response']['message'] = 'Not allowed to change note.';
    $unauthorizedDeleteResult = $unauthorizedResult;
    $unauthorizedDeleteResult['json_response']['message'] = 'Not allowed to delete note.';
    $authorizedCreateResult = [StatusCodeInterface::class => StatusCodeInterface::STATUS_CREATED];
    return [
        [ // ? newcomer not owner
            // User to whom the note (or client for creation) is linked
            'owner_user' => $advisorAttributes,
            'authenticated_user' => $newcomerAttributes,
            'expected_result' => [
                // Allowed to create note on client where user is not owner
                'creation' => $authorizedCreateResult,
                'modification' => [
                    'main_note' => $unauthorizedUpdateResult,
                    'normal_note' => $unauthorizedResult,
                ],
                'deletion' => [
                    // Delete main note request is expected to produce 405 HttpMethodNotAllowedException
                    'normal_note' => $unauthorizedDeleteResult
                ],
            ],
        ],
        [ // ? newcomer owner
            // User to whom the note (or client for creation) is linked
            'owner_user' => $newcomerAttributes,
            'authenticated_user' => $newcomerAttributes,
            'expected_result' => [
                'creation' => [
                    StatusCodeInterface::class => StatusCodeInterface::STATUS_CREATED,
                ],
                'modification' => [
                    // Newcomer may not edit client basic data which has the same rights as the main note
                    'main_note' => $unauthorizedUpdateResult,
                    'normal_note' => $authorizedResult,
                ],
                'deletion' => [
                    // Delete main note request is expected to produce 405 HttpMethodNotAllowedException
                    'normal_note' => $authorizedResult
                ],
            ],
        ],
        [ // ? advisor owner
            // User to whom the note (or client for creation) is linked
            'owner_user' => $advisorAttributes,
            'authenticated_user' => $advisorAttributes,
            'expected_result' => [
                'creation' => [ // Allowed to create note on client where user is not owner
                    StatusCodeInterface::class => StatusCodeInterface::STATUS_CREATED,
                ],
                'modification' => [
                    'main_note' => $authorizedResult,
                    'normal_note' => $authorizedResult,
                ],
                'deletion' => [
                    // Delete main note request is expected to produce 405 HttpMethodNotAllowedException
                    'normal_note' => $authorizedResult
                ],
            ],
        ],
        [ // ? advisor not owner
            // User to whom the note (or client for creation) is linked
            'owner_user' => $managingAdvisorAttributes,
            'authenticated_user' => $advisorAttributes,
            'expected_result' => [
                'creation' => $authorizedCreateResult,
                'modification' => [
                    'main_note' => $authorizedResult,
                    'normal_note' => $unauthorizedUpdateResult,
                ],
                'deletion' => [
                    // Delete main note request is expected to produce 405 HttpMethodNotAllowedException
                    'normal_note' => $unauthorizedDeleteResult
                ],
            ],
        ],
        [ // ? managing advisor not owner
            // User to whom the note (or client for creation) is linked
            'owner_user' => $advisorAttributes,
            'authenticated_user' => $managingAdvisorAttributes,
            'expected_result' => [
                'creation' => $authorizedCreateResult,
                'modification' => [
                    'main_note' => $authorizedResult,
                    'normal_note' => $authorizedResult,
                ],
                'deletion' => [
                    // Delete main note request is expected to produce 405 HttpMethodNotAllowedException
                    'normal_note' => $authorizedResult
                ],
            ],
        ],
    ];
}
```
</details>


<details>
  <summary><h4>Note create action test</h4></summary>

`tests/Integration/Note/NoteCreateActionTest.php`
```php
/**
 * Test main note and normal note update on client-read page while being authenticated
 * with different user roles.
 *
 * @dataProvider \App\Test\Provider\Note\NoteCaseProvider::provideUserAttributesAndExpectedResultForNoteCUD()
 * 
 * @param array $userLinkedToNoteAttr note owner attributes containing the user_role_id
 * @param array $authenticatedUserAttr authenticated user attributes containing the user_role_id
 * @param array $expectedResult HTTP status code, if db is supposed to change and json_response
 * @return void
 */
public function testNoteSubmitCreateAction(
    array $userLinkedToNoteAttr,
    array $authenticatedUserAttr,
    array $expectedResult
): void {
    // Insert authenticated user and user linked to resource with given attributes containing the user role
    $authenticatedUserRow = $this->insertFixturesWithAttributes($authenticatedUserAttr, UserFixture::class);
    if ($authenticatedUserAttr === $userLinkedToNoteAttr) {
        $userLinkedToClientRow = $authenticatedUserRow;
    } else {
        // If authenticated user and owner user is not the same, insert owner
        $userLinkedToClientRow = $this->insertFixturesWithAttributes($userLinkedToNoteAttr, UserFixture::class);
    }
    // Insert needed client status fixture
    $clientStatusId = $this->insertFixturesWithAttributes([], ClientStatusFixture::class)['id'];
    // Insert one client linked to this user
    $clientRow = $this->insertFixturesWithAttributes(
        ['user_id' => $userLinkedToClientRow['id'], 'client_status_id' => $clientStatusId],
        ClientFixture::class
    );
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
    $this->container->get(SessionInterface::class)->set('user_id', $authenticatedUserRow['id']);
    // Make request
    $response = $this->app->handle($request);
    // Assert 201 Created redirect to login url
    self::assertSame($expectedResult['creation'][StatusCodeInterface::class], $response->getStatusCode());
    // Assert database
    // Find freshly inserted note
    $noteDbRow = $this->findLastInsertedTableRow('note');
    // Assert the row column values
    $this->assertTableRow(['message' => $noteMessage, 'is_main' => 0], 'note', (int)$noteDbRow['id']);
    // Assert response
    $expectedResponseJson = [
        'status' => 'success',
        'data' => [
            'userFullName' => $authenticatedUserRow['first_name'] . ' ' . $authenticatedUserRow['surname'],
            'noteId' => $noteDbRow['id'],
            'createdDateFormatted' => $this->dateTimeToClientReadNoteFormat($noteDbRow['created_at']),
        ],
    ];
    $this->assertJsonData($expectedResponseJson, $response);
}
```
</details>

<details>
  <summary><h4>Note update action test</h4></summary>

```php
/**
 * Test note modification on client-read page while being authenticated
 * with different user roles.
 *
 * @dataProvider \App\Test\Provider\Note\NoteCaseProvider::provideUserAttributesAndExpectedResultForNoteCUD()
 * @param array $userLinkedToNoteAttr note owner attributes containing the user_role_id
 * @param array $authenticatedUserAttr authenticated user attributes containing the user_role_id
 * @param array $expectedResult HTTP status code, if db is supposed to change and json_response
 * @return void
 */
public function testNoteSubmitUpdateAction(
    array $userLinkedToNoteAttr,
    array $authenticatedUserAttr,
    array $expectedResult
): void {
    // Insert authenticated user and user linked to resource with given attributes containing the user role
    $authenticatedUserRow = $this->insertFixturesWithAttributes($authenticatedUserAttr, UserFixture::class);
    if ($authenticatedUserAttr === $userLinkedToNoteAttr) {
        $userLinkedToNoteRow = $authenticatedUserRow;
    }else{
        // If authenticated user and owner user is not the same, insert owner
        $userLinkedToNoteRow = $this->insertFixturesWithAttributes($userLinkedToNoteAttr, UserFixture::class);
    }
    // Insert linked status
    $clientStatusId = $this->insertFixturesWithAttributes([], ClientStatusFixture::class)['id'];
    // Insert one client linked to this user
    $clientRow = $this->insertFixturesWithAttributes(
        ['user_id' => $userLinkedToNoteRow['id'], 'client_status_id' => $clientStatusId],
        ClientFixture::class
    );
    // Insert main note attached to client and given "owner" user
    $mainNoteRow = $this->insertFixturesWithAttributes(
        ['is_main' => 1, 'user_id' => $userLinkedToNoteRow['id'], 'client_id' => $clientRow['id']],
        NoteFixture::class
    );
    // Insert normal note attached to client and given "owner" user
    $normalNoteRow = $this->insertFixturesWithAttributes(
        ['is_main' => 0, 'user_id' => $userLinkedToNoteRow['id'], 'client_id' => $clientRow['id']],
        NoteFixture::class
    );
    // Simulate logged-in user
    $this->container->get(SessionInterface::class)->set('user_id', $authenticatedUserRow['id']);
    $newNoteMessage = 'New note message';
    // --- *MAIN note request ---
    // Create request to edit main note
    $mainNoteRequest = $this->createJsonRequest(
        'PUT', $this->urlFor('note-submit-modification', ['note_id' => $mainNoteRow['id']]),
        ['message' => $newNoteMessage,]
    );
    // Make request
    $mainNoteResponse = $this->app->handle($mainNoteRequest);
    // Assert 200 OK note updated successfully
    self::assertSame(
        $expectedResult['modification']['main_note'][StatusCodeInterface::class],
        $mainNoteResponse->getStatusCode()
    );
    if ($expectedResult['modification']['main_note']['db_changed'] === true) {
        $this->assertTableRow(['message' => $newNoteMessage], 'note', $mainNoteRow['id']);
    } else {
        // If db is not expected to change message should remain the same as when it was inserted first
        $this->assertTableRow(['message' => $mainNoteRow['message']], 'note', $mainNoteRow['id']);
    }
    // Assert response
    $this->assertJsonData($expectedResult['modification']['main_note']['json_response'], $mainNoteResponse);
    // --- *NORMAL NOTE REQUEST ---
    $normalNoteRequest = $this->createJsonRequest(
        'PUT', $this->urlFor('note-submit-modification', ['note_id' => $normalNoteRow['id']]),
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
        $this->assertTableRow(['message' => $newNoteMessage], 'note', $normalNoteRow['id']);
    } else {
        // If db is not expected to change message should remain the same as when it was inserted first
        $this->assertTableRow(['message' => $normalNoteRow['message']], 'note', $normalNoteRow['id']);
    }
    $this->assertJsonData($expectedResult['modification']['normal_note']['json_response'], $normalNoteResponse);
}
```
</details>

<details>
  <summary><h4>Note delete action test</h4></summary>

```php
/**
 * Test normal and main note deletion on client-read page
 * while being authenticated with different user roles.
 *
 * @dataProvider \App\Test\Provider\Note\NoteCaseProvider::provideUserAttributesAndExpectedResultForNoteCUD()
 * @param array $userLinkedToNoteAttr note owner attributes containing the user_role_id
 * @param array $authenticatedUserAttr authenticated user attributes containing the user_role_id
 * @param array $expectedResult HTTP status code, if db is supposed to change and json_response
 * @return void
 */
public function testNoteSubmitDeleteAction(
    array $userLinkedToNoteAttr,
    array $authenticatedUserAttr,
    array $expectedResult
): void {
    // Insert authenticated user and user linked to resource with given attributes containing the user role
    $authenticatedUserRow = $this->insertFixturesWithAttributes($authenticatedUserAttr, UserFixture::class);
    if ($authenticatedUserAttr === $userLinkedToNoteAttr) {
        $userLinkedToNoteRow = $authenticatedUserRow;
    }else{
        // If authenticated user and owner user is not the same, insert owner
        $userLinkedToNoteRow = $this->insertFixturesWithAttributes($userLinkedToNoteAttr, UserFixture::class);
    }
    // Insert linked status
    $clientStatusId = $this->insertFixturesWithAttributes([], ClientStatusFixture::class)['id'];
    // Insert one client linked to this user
    $clientRow = $this->insertFixturesWithAttributes(
        ['user_id' => $userLinkedToNoteRow['id'], 'client_status_id' => $clientStatusId],
        ClientFixture::class
    );
    // Insert main note attached to client and given "owner" user
    $mainNoteData = $this->insertFixturesWithAttributes(
        [
            'is_main' => 1,
            'user_id' => $userLinkedToNoteRow['id'],
            'client_id' => $clientRow['id'],
        ],
        NoteFixture::class
    );
    // Insert normal note attached to client and given "owner" user
    $normalNoteData = $this->insertFixturesWithAttributes(
        [
            'is_main' => 0,
            'user_id' => $userLinkedToNoteRow['id'],
            'client_id' => $clientRow['id'],
        ],
        NoteFixture::class
    );
    // Simulate logged-in user
    $this->container->get(SessionInterface::class)->set('user_id', $authenticatedUserRow['id']);
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


## Test validation and assert errors example
Form fields generally have specific criteria like a minimum length or specific format that are validated.
This can and should be tested.
### Generate Validation cases with a case provider
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
### Test validation on resource modification
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

## Test and assert malformed request body example
When the client makes a request and the body has not the right syntax (e.g. wrong key or invalid amount of keys).

In order to not having to write multiple tests, I'm using a data provider:

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