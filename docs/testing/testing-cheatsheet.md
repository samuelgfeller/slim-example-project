# Testing cheatsheet

### Test traits
The needed [test traits](https://github.com/selective-php/test-traits) can be added right after the test class opening
brackets. Here are some of them:
```php
use AppTestTrait; // Custom
use HttpTestTrait;
use HttpJsonTestTrait;
use RouteTestTrait;
use DatabaseTestTrait;
use FixtureTrait; // Custom
```
More on it and the whole testing setup can be found in [testing.md](https://github.com/samuelgfeller/slim-example-project/blob/master/docs/testing/testing.md)
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

To insert only the records that are linked to the main ressource we want to test (or matching another specific criteria),
the function `insertFixtureWhere` can be used like follows:
```php
$clientRow = (new ClientFixture())->records[0];
$this->insertFixture('client', $clientRow);

// HERE - Insert only linked notes
$this->insertFixtureWhere(['client_id' => $clientRow['id']], NoteFixture::class);
```

### Create request
For a json request and assertions later, the `HttpJsonTestTrait.php` is used.
Request is created as follows:
```php
$request = $this->createJsonRequest('GET', $this->urlFor('note-list'))->withQueryParams(['client_id' => 1]);
```
Very important note here, query params can be added directly in the `urlFor()` method, like
`$this->createJsonRequest('GET', $this->urlFor('note-list', [], ['client_id' => 1]))` but 
**[that won't work](https://github.com/Nyholm/psr7/issues/181) if `nyholm/psr7`
is used in the project.** So we have to explicitly use `->withQueryParams(['client_id' => 1])` like shown above.

### Assert Json response
If we want to test the notes attached to a client that are loaded via Ajax after a client-read request, I would firstly
get all relevant note rows like follows:
```php
$noteRows = $this->findRecordsFromFixtureWhere(['is_main' => 0, 'client_id' => $clientRow['user_id']], NoteFixture::class);
```

Then, as we have multiple notes, init the `$expectedResponseArray` and loop over the ressource under test `$noteRows`.  
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

#### Assert user rights
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
        'userMutationRight' => $hasMutationRight($loggedInUserRow['role'], $noteRow['user_id']),
    ];
}
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
#### Assert response with `Redirect-to-route-name-if-unauthorized`
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
#### Assert response without custom header
This is basically the same as [non-authenticated page action test](#non-authenticated-page-action-test). 

