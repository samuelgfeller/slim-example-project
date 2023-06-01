# Programming cheatsheet with quick examples

Testing cheatsheet: testing/testing-cheatsheet.md
```php
// this is temporary and only so that PHPStorm knows that it's a path and to open the file with mouse wheel click
require 'C:\xampp\htdocs\slim-example-project\docs\testing\testing-cheatsheet.md'
```

## Database migrations
* After changing the database before testing the schema.sql has to be update `composer schema:generate`.
* When satisfied with the changes and ready to commit, new migration files have to be generated
`composer migration:generate` before being pushed to the version control.
* When pulling from the remote and other devs made database changes, `composer migrate` has to be executed. 
Then (as it's a database change), run `composer schema:generate` to update the schema.sql (for testing).
* After deploying `composer migrate` has to be executed on the remote server to update the database.

## Making Ajax requests
### GET request
```js
let clientId = document.getElementById('client-id').value;
let queryParams = 'client_id=' + clientId;

let xHttp = new XMLHttpRequest();
xHttp.onreadystatechange = function () {
    if (xHttp.readyState === XMLHttpRequest.DONE) {
        // Fail
        if (xHttp.status !== 200) {
            // Default fail handler
            handleFail(xHttp);
        }
        // Success
        else {
            let parsedResponse = JSON.parse(xHttp.responseText);
            removeClientNoteContentPlaceholder();
            callbackFunction();
        }
    }
};

// For GET requests, query params have to be passed in the url directly. They are ignored in send()
xHttp.open('GET', basePath + 'notes?' + queryParams, true);
xHttp.setRequestHeader("Content-type", "application/json");
// Adding content type json and "Redirect-to-url-if-unauthorized" header for the UserAuthenticationMiddleware
// to know to send the login url in the json response body and where to redirect back after a successful login
xHttp.setRequestHeader("Redirect-to-url-if-unauthorized", basePath + "client/" + clientId);
xHttp.send();
```

### POST request
```js
let xHttp = new XMLHttpRequest();
xHttp.onreadystatechange = function () {
    if (xHttp.readyState === XMLHttpRequest.DONE) {
        // Fail
        if (xHttp.status !== 201 && xHttp.status !== 200) {
            // Default fail handler
            handleFail(xHttp);
        }
        // Success
        else {
            let responseData = JSON.parse(xHttp.responseText);
            if (response.status === 'success') {
                // Do something with response data like adding them to the DOM 
            }
        }
    }
};
xHttp.open('POST', basePath + 'notes', true);
xHttp.setRequestHeader("Content-type", "application/json");
// Redirect back to the client read page if client was logged out in the meantime and had to re-login
xHttp.setRequestHeader("Redirect-to-url-if-unauthorized", basePath + "client/" + document.getElementById('client-id').value);
// Data format: "fname=Henry&lname=Ford"
// In [square brackets] to be evaluated
xHttp.send(JSON.stringify({
    [textarea.name]: textarea.value,
    // Not camelCase as html form names are underline too
    client_id: document.getElementById('client-id').value
}));
```
**Note**: as seen above the custom request HTTP header `Redirect-to-url-if-unauthorized` is added. There is another custom
header supported by the backend when the route can easily be created with the route name: `Redirect-to-route-name-if-unauthorized`.

## Style 