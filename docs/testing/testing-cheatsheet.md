# Testing cheatsheet

## Start testing

I strongly recommend (also to my future self) to write a bullet point list of exactly what should be tested for each
page
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
          Expected result may depend on each role. Often multiple roles have the same "result". If every role can see
          the
          same thing I would not write different test cases
    * Unauthenticated page load
      Expected: redirect to login page with correct query parameters to redirect back to previous page
* Ajax resource loading (sub resource loaded via Ajax like notes loaded on the client read page)
    * Authenticated load
        * *Sub resource data load is most often covered by the authenticated page load test so not necessary.*
        * Load sub resource with different roles may be interesting if items returned in response body differ
          depending on the role of the logged-in user (asserting `$privilege` for instance)
            * Load with every different type of user role. Ideally and if well maintained, only the roles where there
              are
              relevant changes to be tested [example](#provider-for-note-list-action-test)
        * Test that deleted resource is NOT in response
    * Unauthenticated load  
      Expected: Correct status code (401) and login url in response body with correct query parameters that include url
      to the previous page
* Ajax resource creation / modification / deletion
    * Authenticated creation / modification / deletion submission
        * Authorization (privilege): creation / modification / deletion submit with each different user role as
          authenticated user
            1. Each role as resource owner (main resource owner for creation) - *e.g. role "newcomer" and owner, "
               advisor" and owner etc.*
            2. Each role NOT as owner - *e.g. role "newcomer" and not owner, "advisor" and not owner etc.*
                * Not every role is needed as roles work in a hierarchical way. It doesn't have to be tested further
                  than the lowest
                  privilege that is allowed to perform action when not owner. *e.g. "admin" can do at least everything "
                  managing_advisor" can do*
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
use FixtureTestTrait; // Custom

public function testClientReadPageAction_authenticated(): void
{
    // Insert linked and authenticated user
    $userId = $this->insertFixturesWithAttributes([], UserFixture::class)['id'];
    // Insert linked client status
    $clientStatusId = $this->insertFixturesWithAttributes([], ClientStatusFixture::class)['id'];
    // Add needed database values to correctly display the page
    $clientRow = $this->insertFixturesWithAttributes(['user_id' => $userId, 'client_status_id' => $clientStatusId],
    
    // Simulate logged-in user with logged-in user id
    $this->container->get(SessionInterface::class)->set('user_id', $userId);
        ClientFixture::class);
        
    $request = $this->createRequest('GET', $this->urlFor('client-read-page', ['client_id' => 1]));
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

At first, the needed test data has to be inserted into the database. This is made by the awesome `DatabaseTestTrait.php`
.  
To be able to test more agilely with fixtures, I created the `FixtureTestTrait.php`:

```php
use FixtureTestTrait;
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

To insert only the records that matching specific criteria, the function `insertFixturesWithAttributes` can be used
like follows:

```php
$clientOwnerId = $this->insertFixturesWithAttributes(['first_name' => 'Josh'], UserFixture::class)['id'];
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

To be able to use extended select functions, `use DatabaseExtensionTestTrait;` has to be added after `DatabaseTestTrait`
.

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

Test function, provider and
assertions: **[Test validation and assert errors example](#test-validation-and-assert-errors-example)**.

### Test and assert malformed request body

When the client makes a request and the body has not the right syntax (e.g. wrong key or invalid amount of keys)
the server should respond with 400 Bad Request.

Test function, provider and assertions:
**[Malformed request body provider and test function example](#malformed-request-body-provider-and-test-function-example).**

## Test and assert JSON response when unauthenticated

When protected Ajax request is sent to the server and user is not logged-in, the client should redirect the user to
the login form. The redirect
action [cannot be initiated by the server](https://github.com/odan/slim4-tutorial/issues/44),
so it has to be done by the client.
This is the simplified code inside the default Ajax handleFail() method:

```js
// Not logged in, redirect to login url
if (xhr.status === 401) {
    window.location.href = JSON.parse(xhr.responseText).loginUrl;
}
```

Not only should the client be redirected to the login page, but also redirected back to the where he/she was before
having
to log in again. To make this possible, the server has to respond with the full login url with redirect back query
params.   
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
  <summary><h4>Assert response with <code>Redirect-to-route-name-if-unauthorized</code></h4></summary>

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

### Authorization test trait

User roles are stored in the database and have unique ids. When working in the code though, we use the `UserRole`
Enum as this provides clear cases so that the id doesn't have to be hardcoded.  
`AuthorizationTestTrait` makes it easy to provide user attributes with the Enum cases as user role like
`['user_role_id' => UserRole::ADMIN]`. In the test function this user role id attribute can be replaced with
the actual correct id with the function `$this->addUserRoleId($userAttr)`.  
Most of the time 2 users have to be inserted, one which is the resource owner and the other the authenticated user.
Both can be inserted with the Enum case as `user_role_id` with the following function:

```php
// Change user attributes to user data
$this->insertUserFixturesWithAttributes($userRow, $authenticatedUserRow);
```

For prettier code parameters references meaning the function can change the provided arguments values which it does.
Expected are user attributes in an array like in the example above and those attributes are replaced with the
inserted user data.

### Data loading

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
    array $userLinkedToNoteRow,
    array $authenticatedUserRow,
    array $expectedResult
): void {
    // Insert authenticated user and user linked to resource with given attributes containing the user role
    $this->insertUserFixturesWithAttributes($userLinkedToNoteRow, $authenticatedUserRow);
    
    // As the client owner is not relevant, another user (advisor) is taken. If this test fails in the future
    // because note read rights change (e.g. that newcomers may not see the notes from everyone), the
    // client owner id has to be added to the provider
    $clientOwnerId = $this->insertFixturesWithAttributes(
        $this->addUserRoleId(['user_role_id' => 3]), 
        UserFixture::class
    )['id'];
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
    $managingAdvisorAttr = ['user_role_id' => UserRole::MANAGING_ADVISOR];
    $advisorAttr = ['user_role_id' => UserRole::ADVISOR];
    $newcomerAttr = ['user_role_id' => UserRole::NEWCOMER];
    return [
        [// ? newcomer not owner of note
            'note_owner' => $advisorAttr,
            'authenticated_user' => $newcomerAttr,
            'expected_result' => [
                StatusCodeInterface::class => StatusCodeInterface::STATUS_OK,
                'privilege' => Privilege::CREATE
            ],
        ],
        [// ? newcomer owner of note
            'note_owner' => $newcomerAttr,
            'authenticated_user' => $newcomerAttr,
            'expected_result' => [
                StatusCodeInterface::class => StatusCodeInterface::STATUS_OK,
                'privilege' => Privilege::DELETE
            ],
        ],
        // Advisor owner would be the same as newcomer
        [// ? managing advisor not owner of note
            'note_owner' => $advisorAttr,
            'authenticated_user' => $managingAdvisorAttr,
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

Client read is basically only a page action test as the values are rendered by the server and not client.
Client list however is rendered by the client via Ajax request, it's good for testing.

Client list test action

</details>

### Basic data mutation (create, update and delete actions) with one provider

To test the behaviour of all relevant different user roles a data provider is used that returns the relevant user
attributes
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
    $managingAdvisorAttr = ['user_role_id' => UserRole::MANAGING_ADVISOR];
    $advisorAttr = ['user_role_id' => UserRole::ADVISOR];
    $newcomerAttr = ['user_role_id' => UserRole::NEWCOMER];
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
            'owner_user' => $advisorAttr,
            'authenticated_user' => $newcomerAttr,
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
            'owner_user' => $newcomerAttr,
            'authenticated_user' => $newcomerAttr,
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
            'owner_user' => $advisorAttr,
            'authenticated_user' => $advisorAttr,
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
            'owner_user' => $managingAdvisorAttr,
            'authenticated_user' => $advisorAttr,
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
            'owner_user' => $advisorAttr,
            'authenticated_user' => $managingAdvisorAttr,
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
 * @param array $userLinkedToClientRow note owner attributes containing the user_role_id
 * @param array $authenticatedUserRow authenticated user attributes containing the user_role_id
 * @param array $expectedResult HTTP status code, if db is supposed to change and json_response
 * @return void
 */
public function testNoteSubmitCreateAction(
    array $userLinkedToClientRow,
    array $authenticatedUserRow,
    array $expectedResult
): void {
    // Insert authenticated user and user linked to resource with given attributes containing the user role
    $this->insertUserFixturesWithAttributes($userLinkedToClientRow, $authenticatedUserRow);

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

`tests/Integration/Note/NoteUpdateActionTest.php`

```php
/**
 * Test note modification on client-read page while being authenticated
 * with different user roles.
 *
 * @dataProvider \App\Test\Provider\Note\NoteCaseProvider::provideUserAttributesAndExpectedResultForNoteCUD()
 * @param array $userLinkedToNoteRow note owner attributes containing the user_role_id
 * @param array $authenticatedUserRow authenticated user attributes containing the user_role_id
 * @param array $expectedResult HTTP status code, if db is supposed to change and json_response
 * @return void
 */
public function testNoteSubmitUpdateAction(
    array $userLinkedToNoteRow,
    array $authenticatedUserRow,
    array $expectedResult
): void {
    // Insert authenticated user and user linked to resource with given attributes containing the user role
    $this->insertUserFixturesWithAttributes($userLinkedToNoteRow, $authenticatedUserRow);

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

`tests/Integration/Note/NoteDeleteActionTest.php`

```php
/**
 * Test normal and main note deletion on client-read page
 * while being authenticated with different user roles.
 *
 * @dataProvider \App\Test\Provider\Note\NoteCaseProvider::provideUserAttributesAndExpectedResultForNoteCUD()
 * @param array $userLinkedToNoteRow note owner attributes containing the user_role_id
 * @param array $authenticatedUserRow authenticated user attributes containing the user_role_id
 * @param array $expectedResult HTTP status code, if db is supposed to change and json_response
 * @return void
 */
public function testNoteSubmitDeleteAction(
    array $userLinkedToNoteRow,
    array $authenticatedUserRow,
    array $expectedResult
): void {
    // Insert authenticated user and user linked to resource with given attributes containing the user role
    $this->insertUserFixturesWithAttributes($userLinkedToNoteRow, $authenticatedUserRow);
        
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

### Data mutation with individual provider

For more complex cases where each test function has different relevant test parameters and privileges between
create, read, update or delete actions and which column is targeted.

<details>
  <summary><h4>Client create action test and provider</h4></summary>

`tests/Integration/Client/ClientCreateActionTest.php`

```php
/**
 * Client creation with valid data
 *
 * @dataProvider \App\Test\Provider\Client\ClientCreateCaseProvider::provideUsersAndExpectedResultForClientCreation()
 *
 * @param array $userLinkedToClientRow client owner attributes containing the user_role_id
 * @param array $authenticatedUserRow authenticated user attributes containing the user_role_id
 * @param array $expectedResult HTTP status code, bool if db_entry_created and json_response
 * @return void
 */
public function testClientSubmitCreateAction_authorization(
    array $userLinkedToClientRow,
    array $authenticatedUserRow,
    array $expectedResult
): void {
    // Insert authenticated user and user linked to resource with given attributes containing the user role
    $this->insertUserFixturesWithAttributes($userLinkedToClientRow, $authenticatedUserRow);
    
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
    } else {
        // 0 rows expected in client table
        $this->assertTableRowCount(0, 'client');
    }
    $this->assertJsonData($expectedResult['json_response'], $response);
}
```

`tests/Provider/Client/ClientCreateCaseProvider.php`

```php
public function provideUsersAndExpectedResultForClientCreation(): array
{
    // Get users with different roles
    $managingAdvisorAttr = ['user_role_id' => UserRole::MANAGING_ADVISOR];
    $advisorAttr = ['user_role_id' => UserRole::ADVISOR];
    $newcomerAttr = ['user_role_id' => UserRole::NEWCOMER];
    $authorizedResult = [
        StatusCodeInterface::class => StatusCodeInterface::STATUS_CREATED,
        'db_entry_created' => true,
        'json_response' => [
            'status' => 'success',
            'data' => null,
        ],
    ];
    $unauthorizedResult = [
        StatusCodeInterface::class => StatusCodeInterface::STATUS_FORBIDDEN,
        'db_entry_created' => false,
        'json_response' => [
            'status' => 'error',
            'message' => 'Not allowed to create a client.',
        ]
    ];
    return [
        // "owner" means from the perspective of the authenticated user
        [ // ? Newcomer owner - not allowed
            'user_linked_to_client' => $newcomerAttr,
            'authenticated_user' => $newcomerAttr,
            'expected_result' => $unauthorizedResult
        ],
        [ // ? Advisor owner - allowed
            'user_linked_to_client' => $advisorAttr,
            'authenticated_user' => $advisorAttr,
            'expected_result' => $authorizedResult,
        ],
        [ // ? Advisor not owner - not allowed
            'user_linked_to_client' => $newcomerAttr,
            'authenticated_user' => $advisorAttr,
            'expected_result' => $unauthorizedResult,
        ],
        [ // ? Managing not owner - allowed
            'user_linked_to_client' => $advisorAttr,
            'authenticated_user' => $managingAdvisorAttr,
            'expected_result' => $authorizedResult,
        ],
    ];
}
```

</details>

<details>
  <summary><h4>Client update action test and provider</h4></summary>

As there are different authorization rules for some columns, the data to be changed is also passed via data
provider.

`tests/Integration/Client/ClientUpdateActionTest.php`

```php
/**
 * Test client values update when authenticated with different user roles.
 *
 * @dataProvider \App\Test\Provider\Client\ClientUpdateCaseProvider::provideUsersAndExpectedResultForClientUpdate
 *
 * @param array $userLinkedToClientRow client owner attributes containing the user_role_id
 * @param array $authenticatedUserRow authenticated user attributes containing the user_role_id
 * @param array $requestData array of data for the request body
 * @param array $expectedResult HTTP status code, bool if db_entry_created and json_response
 * @return void
 */
public function testClientSubmitUpdateAction_authenticated(
    array $userLinkedToClientRow,
    array $authenticatedUserRow,
    array $requestData,
    array $expectedResult
): void {
    // Insert authenticated user and user linked to resource with given attributes containing the user role
    $this->insertUserFixturesWithAttributes($userLinkedToClientRow, $authenticatedUserRow);
    
    // Insert client status
    $clientStatusId = $this->insertFixturesWithAttributes([], ClientStatusFixture::class)['id'];
    // Insert client that will be used for this test
    $clientRow = $this->insertFixturesWithAttributes(
        ['client_status_id' => $clientStatusId, 'user_id' => $userLinkedToClientRow['id']],
        ClientFixture::class
    );
    // Insert other user and client status that are used for the modification request if needed
    if (isset($requestData['user_id'])) {
        // Add 1 to user_id linked to client
        $requestData['user_id'] = $clientRow['user_id'] + 1;
        $this->insertFixturesWithAttributes(['id' => $requestData['user_id']], UserFixture::class);
    }
    if (isset($requestData['client_status_id'])) {
        // Add 1 to client status id
        $requestData['client_status_id'] = $clientRow['client_status_id'] + 1;
        $this->insertFixturesWithAttributes(['id' => $requestData['client_status_id']], ClientStatusFixture::clas
    }
    $request = $this->createJsonRequest(
        'PUT',
        $this->urlFor('client-submit-update', ['client_id' => $clientRow['id']]),
        $requestData
    );
    // Simulate logged-in user with logged-in user id
    $this->container->get(SessionInterface::class)->set('user_id', $authenticatedUserRow['id']);
    $response = $this->app->handle($request);
    // Assert status code
    self::assertSame($expectedResult[StatusCodeInterface::class], $response->getStatusCode());
    // Assert database
    if ($expectedResult['db_changed'] === true) {
        // HTML form element names are the same as the database columns, the same request array can be taken to assert the db
        // Check that data in request body was changed
        $this->assertTableRowEquals($requestData, 'client', $clientRow['id']);
    } else {
        // If db is not expected to change, data should remain the same as when it was inserted from the fixture
        $this->assertTableRowEquals($clientRow, 'client', $clientRow['id']);
    }
    $this->assertJsonData($expectedResult['json_response'], $response);
}
```

`tests/Provider/Client/ClientUpdateCaseProvider.php`

```php
public function provideUsersAndExpectedResultForClientUpdate(): array
{
    // Set different user role attributes
    $managingAdvisorAttr = ['user_role_id' => UserRole::MANAGING_ADVISOR];
    $advisorAttr = ['user_role_id' => UserRole::ADVISOR];
    $newcomerAttr = ['user_role_id' => UserRole::NEWCOMER];
    $authorizedResult = [
        StatusCodeInterface::class => StatusCodeInterface::STATUS_OK,
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
            'message' => 'Not allowed to update client.',
        ]
    ];
    $basicClientDataChanges = [
        'first_name' => 'NewFirstName',
        'last_name' => 'NewLastName',
        'birthdate' => '1999-10-22',
        'location' => 'NewLocation',
        'phone' => '011 111 11 11',
        'email' => 'new.email@test.ch',
        'sex' => 'O',
    ];
    // To avoid testing each column separately for each user role, the most basic change is taken to test
    // [foreign_key => 'new'] will be replaced in test function as user has to be added to the database
    return [
        // * Newcomer
        // "owner" means from the perspective of the authenticated user
        [ // ? Newcomer owner - data to be changed is the one with the least privilege needed - not allowed
            'user_linked_to_client' => $newcomerAttr,
            'authenticated_user' => $newcomerAttr,
            'data_to_be_changed' => ['first_name' => 'value'],
            'expected_result' => $unauthorizedResult
        ],
        // * Advisor
        [ // ? Advisor owner - data to be changed allowed
            'user_linked_to_client' => $advisorAttr,
            'authenticated_user' => $advisorAttr,
            'data_to_be_changed' => array_merge(['client_status_id' => 'new'], $basicClientDataChanges),
            'expected_result' => $authorizedResult,
        ],
        [ // ? Advisor owner - data to be changed not allowed
            'user_linked_to_client' => $advisorAttr,
            'authenticated_user' => $advisorAttr,
            'data_to_be_changed' => ['user_id' => 'new'],
            'expected_result' => $unauthorizedResult,
        ],
        [ // ? Advisor not owner - data to be changed allowed
            'user_linked_to_client' => $managingAdvisorAttr,
            'authenticated_user' => $advisorAttr,
            'data_to_be_changed' => $basicClientDataChanges,
            'expected_result' => $authorizedResult,
        ],
        [ // ? Advisor not owner - data to be changed not allowed
            'user_linked_to_client' => $managingAdvisorAttr,
            'authenticated_user' => $advisorAttr,
            'data_to_be_changed' => ['client_status_id' => 'new'],
            'expected_result' => $unauthorizedResult,
        ],
        // * Managing advisor
        [ // ? Managing advisor not owner - there is no data change that is not allowed for managing advisor
            'user_linked_to_client' => $advisorAttr,
            'authenticated_user' => $managingAdvisorAttr,
            'data_to_be_changed' => array_merge(
                $basicClientDataChanges,
                ['client_status_id' => 'new', 'user_id' => 'new']
            ),
            'expected_result' => $authorizedResult,
        ],
    ];
}
```

</details>

<details>
  <summary><h4>Client delete action test and provider</h4></summary>

`tests/Integration/Client/ClientDeleteActionTest.php`

```php
/**
 * Test delete client submit with different authenticated user roles.
 *
 * @dataProvider \App\Test\Provider\Client\ClientDeleteCaseProvider::provideUsersForClientDelete()
 *
 * @param array $userLinkedToClientRow client owner attributes containing the user_role_id
 * @param array $authenticatedUserRow authenticated user attributes containing the user_role_id
 * @param array $expectedResult HTTP status code, bool if db_entry_created and json_response
 * @return void
 */
public function testClientSubmitDeleteAction_authenticated(
    array $userLinkedToClientRow,
    array $authenticatedUserRow,
    array $expectedResult
): void {
    // Insert authenticated user and user linked to resource with given attributes containing the user role
    $this->insertUserFixturesWithAttributes($userLinkedToClientRow, $authenticatedUserRow);
    // Insert client status
    $clientStatusId = $this->insertFixturesWithAttributes([], ClientStatusFixture::class)['id'];
    // Insert client linked to given user
    $clientRow = $this->insertFixturesWithAttributes(
        ['client_status_id' => $clientStatusId, 'user_id' => $userLinkedToClientRow['id']],
        ClientFixture::class
    );
    
    // Simulate logged-in user
    $this->container->get(SessionInterface::class)->set('user_id', $authenticatedUserRow['id']);
    $request = $this->createJsonRequest(
        'DELETE',
        // Post delete route with id like /posts/1
        $this->urlFor('client-submit-delete', ['client_id' => $clientRow['id']]),
    );
    $response = $this->app->handle($request);
    // Assert: 200 OK
    self::assertSame($expectedResult[StatusCodeInterface::class], $response->getStatusCode());
    // Assert database
    if ($expectedResult['db_changed'] === true) {
        // Assert that deleted_at is NOT null
        self::assertNotNull($this->getTableRowById('client', $clientRow['id'], ['deleted_at']));
    } else {
        // If db is not expected to change, data should remain the same as when it was inserted from the fixture
        $this->assertTableRow(['deleted_at' => null], 'client', $clientRow['id']);
    }
    // Assert response json content
    $this->assertJsonData($expectedResult['json_response'], $response);
}
```

`tests/Provider/Client/ClientDeleteCaseProvider.php`

```php
public function provideUsersForClientDelete(): array
    {
        // Get users with different roles
        $managingAdvisorAttr = ['user_role_id' => UserRole::MANAGING_ADVISOR];
        $advisorAttr = ['user_role_id' => UserRole::ADVISOR];
        $newcomerAttr = ['user_role_id' => UserRole::NEWCOMER];
        $authorizedResult = [
            StatusCodeInterface::class => StatusCodeInterface::STATUS_OK,
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
                'message' => 'Not allowed to delete client.',
            ]
        ];
        // Permissions for deletion are quite simple: only managing advisors and higher may delete clients
        return [
            // * Newcomer
            [ // ? Newcomer owner - not allowed
                // Technically this test case is not relevant as higher hierarchy role is also not allowed to perform action  
                'user_linked_to_client' => $newcomerAttr,
                'authenticated_user' => $newcomerAttr,
                'expected_result' => $unauthorizedResult
            ],
            // * Advisor
            [ // ? Advisor owner - not allowed
                'user_linked_to_client' => $advisorAttr,
                'authenticated_user' => $advisorAttr,
                'expected_result' => $unauthorizedResult,
            ],
            // * Managing advisor
            [ // ? Managing advisor not owner - allowed
                'user_linked_to_client' => $advisorAttr,
                'authenticated_user' => $managingAdvisorAttr,
                'expected_result' => $authorizedResult,
            ],
        ];
    }
```

</details>

## Test validation and assert errors example

Form fields generally have specific criteria like a minimum length or specific format that are validated on the server.
This has to be tested.

### Generate validation cases with a case provider

To be able to test different invalid inputs in one test, the different cases are provided via data provider.  
For creation and modification the validity rules are the same so one provider can be used for both in this case.
But if the fields have disparities it may very well be necessary to use a provider for each action.

<details>
  <summary><h4>Case provider <code>tests/Provider/Client/ClientCreateUpdateCaseProvider.php</code></h4></summary>

```php
public function invalidClientValuesAndExpectedResponseData(): array
{
    // The goal is to include as many values as possible that should trigger validation errors in each iteration
    return [
        [
            // Most values too short
            'request_body' => [
                'first_name' => 'T',
                'last_name' => 'A',
                'birthdate' => '1850-01-01', // too old
                'location' => 'La',
                'phone' => '07',
                'email' => 'test@test', // missing extension
                'sex' => 'A', // invalid value
                'user_id' => '999', // non-existing user
                'client_status_id' => '999', // non-existing status
            ],
            'json_response' => [
                'status' => 'error',
                'message' => 'Validation error',
                'data' => [
                    'message' => 'There is something in the client data that couldn\'t be validated',
                    'errors' => [
                        0 => [
                            'field' => 'client_status',
                            'message' => 'Client_status not existing',
                        ],
                        1 => [
                            'field' => 'user',
                            'message' => 'User not existing',
                        ],
                        2 => [
                            'field' => 'first_name',
                            'message' => 'Required minimum length is 2',
                        ],
                        3 => [
                            'field' => 'last_name',
                            'message' => 'Required minimum length is 2',
                        ],
                        4 => [
                            'field' => 'email',
                            'message' => 'Invalid email address',
                        ],
                        5 => [
                            'field' => 'birthdate',
                            'message' => 'Invalid birthdate',
                        ],
                        6 => [
                            'field' => 'location',
                            'message' => 'Required minimum length is 3',
                        ],
                        7 => [
                            'field' => 'phone',
                            'message' => 'Required minimum length is 3',
                        ],
                        8 => [
                            'field' => 'sex',
                            'message' => 'Invalid sex value given. Allowed are M, F and O',
                        ],
                    ]
                ]
            ]
        ],
        [
            // Most values too long
            'request_body' => [
                'first_name' => str_repeat('i', 101), // 101 chars
                'last_name' => str_repeat('i', 101),
                'birthdate' => (new \DateTime())->modify('+1 day')->format('Y-m-d'), // 1 day in the future
                'location' => str_repeat('i', 101),
                'phone' => '+41 0071 121 12 12 12', // 21 chars
                'email' => 'test$@test.ch', // invalid email
                'sex' => '', // empty string
                // All keys are needed as same dataset is used for create which always expects all keys
                // and the json_response has to be equal too so the value can't be null.
                'user_id' => '999', // non-existing user
                'client_status_id' => '999', // non-existing status
            ],
            'json_response' => [
                'status' => 'error',
                'message' => 'Validation error',
                'data' => [
                    'message' => 'There is something in the client data that couldn\'t be validated',
                    'errors' => [
                        0 => [
                            'field' => 'client_status',
                            'message' => 'Client_status not existing',
                        ],
                        1 => [
                            'field' => 'user',
                            'message' => 'User not existing',
                        ],
                        2 => [
                            'field' => 'first_name',
                            'message' => 'Required maximum length is 100',
                        ],
                        3 => [
                            'field' => 'last_name',
                            'message' => 'Required maximum length is 100',
                        ],
                        4 => [
                            'field' => 'birthdate',
                            'message' => 'Invalid birthdate',
                        ],
                        5 => [
                            'field' => 'location',
                            'message' => 'Required maximum length is 100',
                        ],
                        6 => [
                            'field' => 'phone',
                            'message' => 'Required maximum length is 20',
                        ],
                    ]
                ]
            ]
        ]
    ];
}
```

</details>

### Test validation on resource modification

This is the full test where a client is edited with invalid inputs given by the data provider above.

<details>
  <summary><h4>Test function <code>tests/Integration/Client/ClientUpdateActionTest.php</code></h4></summary>

```php
/**
 * Test client values validation.
 *
 * @dataProvider \App\Test\Provider\Client\ClientCreateUpdateCaseProvider::invalidClientValuesAndExpectedResponseData()
 * @param array $requestBody
 * @param array $jsonResponse
 * @return void
 */
public function testClientSubmitUpdateAction_invalid(array $requestBody, array $jsonResponse): void
{
    // Insert user that is allowed to change content
    $userId = $this->insertFixturesWithAttributes(
        $this->addUserRoleId(['user_role_id' => UserRole::MANAGING_ADVISOR]), 
        UserFixture::class
    )['id'];
    $clientStatusId = $this->insertFixturesWithAttributes([], ClientStatusFixture::class)['id'];
    // Insert client that will be used for this test
    $clientRow = $this->insertFixturesWithAttributes(['client_status_id' => $clientStatusId, 'user_id' => $userId],
        ClientFixture::class);
    $request = $this->createJsonRequest(
        'PUT',
        $this->urlFor('client-submit-update', ['client_id' => 1]),
        $requestBody
    );
    // Simulate logged-in user with logged-in user id
    $this->container->get(SessionInterface::class)->set('user_id', $clientRow['user_id']);
    $response = $this->app->handle($request);
    // Assert 200 OK
    self::assertSame(StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    // database should be unchanged
    $this->assertTableRowEquals($clientRow, 'client', $clientRow['id']);
    $this->assertJsonData($jsonResponse, $response);
}
```

</details>

## Test and assert malformed request body example

When the client makes a request and the body has not the right syntax (e.g. wrong key or invalid amount of keys)
the server should respond with 400 Bad Request.

The different combination of malformed request body are provided via data provider. It doesn't have to be
so extensive though, I don't know if I will implement it so thoroughly for each module but that would cover most cases.

### Malformed request body provider and test function example

<details>
  <summary><h4>Case provider <code>tests/Provider/Note/NoteCaseProvider.php</code></h4></summary>

```php
public function provideNoteMalformedRequestBodyForCreation(): array
{
    return [
        [
            // Empty body
            'requestBody' => [],
        ],
        [
            // Body "null" (because both can happen )
            'requestBody' => null,
        ],
        [
            'requestBody' => [
                'message_wrong' => 'Message', // wrong message key name
                'client_id' => 1,
                'is_main' => 1,
            ],
        ],
        [
            'requestBody' => [
                'message' => 'Message',
                'client_id_wrong' => 1, // wrong client id
                'is_main' => 1,
            ],
        ],
        [
            'requestBody' => [
                'message' => 'Message',
                'client_id' => 1,
                'is_main_wrong' => 1, // wrong is_main
            ],
        ],
        [
            'requestBody' => [ // One key too much
                'message' => 'Message',
                'client_id' => 1,
                'is_main' => 1,
                'extra_key' => 1, // wrong is_main
            ],
        ],
        [
            'requestBody' => [ // Missing is_main
                'message' => 'Message',
                'client_id' => 1,
            ],
        ],
    ];
}
```

</details>

<details>
  <summary><h4>Test function <code>tests/Integration/Note/NoteCreateActionTest.php</code></h4></summary>

```php
/**
 * Test client read note creation with different 
 * combinations of malformed request body.
 *
 * @dataProvider \App\Test\Provider\Note\NoteCaseProvider::provideNoteMalformedRequestBodyForCreation()
 * @param array $malformedRequestBody
 * @return void
 */
public function testNoteSubmitCreateAction_malformedRequest(array $malformedRequestBody): void
{
    // Action class should directly return error so only logged-in user has to be inserted
    $userData = $this->insertFixturesWithAttributes([], UserFixture::class);
    // Simulate logged-in user with same user as linked to client
    $this->container->get(SessionInterface::class)->set('user_id', $userData['id']);
    $request = $this->createJsonRequest(
        'POST',
        $this->urlFor('note-submit-creation'),
        $malformedRequestBody
    );
    // Bad Request (400) means that the client sent the request wrongly; it's a client error
    $this->expectException(HttpBadRequestException::class);
    $this->expectExceptionMessage('Request body malformed.');
    // Handle request after defining expected exceptions
    $this->app->handle($request);
}
```

</details>

-----

[//]: # (## More examples)
