# Programming cheatsheet with quick examples

<details>
  <summary><h2>Database migrations</h2></summary>

* After changing the database before testing the schema.sql has to be update `composer schema:generate`.
* When satisfied with the changes and ready to commit, new migration files have to be generated
  `composer migration:generate` before being pushed to the version control.
* When pulling from the remote and other devs made database changes, `composer migrate` has to be executed.
  Then (as it's a database change), run `composer schema:generate` to update the schema.sql (for testing).
* After deploying `composer migrate` has to be executed on the remote server to update the database.

</details>

<details>
  <summary><h2>New version release</h2></summary>

1. Update the version in the config file: `defaults.php` 
</details>

<details>
  <summary><h2>Ajax requests</h2></summary>

### Fetch data: GET request

```js
fetchData('clients' + '?param=value&param2=value2', 'clients/list').then(jsonResponse => {
    // Doing something with the jsonResponse
}).catch(error => {
    console.error(error);
});;
```
#### `fetchData()` in `public/assets/general/ajax/fetch-data.js`
```js
/**
 * Sends a GET request and returns result in promise
 *
 * @param {string} route only the part after base path ('users/1'). Query params have to be added with ?param=value
 * @param {boolean|string} redirectToRouteIfUnauthenticated true or redirect route url after base path.
 * If true, the redirect url is the same as the given route
 * @return {Promise<JSON>}
 */
function fetchData(route, redirectToRouteIfUnauthenticated = false) {
    // ...
}
```

### Update data: PUT request

```js
submitUpdate({[inputField.name]: inputField.value}, `clients/${clientId}`, true).then(jsonParsedResponse => {
}).catch(e => {
});
```

#### `updateData()` in `public/assets/general/ajax/submit-update-data.js`

```js
/**
 * Send PUT update request.
 * Fail handled by handleFail() method that supports forms
 * On success validation errors are removed and response content returned
 *
 * @param {object} formFieldsAndValues {field: value} e.g. {[input.name]: input.value}
 * @param {string} route after base path e.g. clients/1
 * @param {boolean|string} redirectToRouteIfUnauthenticated true or redirect route url after base path.
 * If true, the redirect url is the same as the given route
 *
 * @return Promise with as content server response as JSON
 */
export function submitUpdate(formFieldsAndValues, route, redirectToRouteIfUnauthenticated = false) {
    // ...
}
```
### Delete data: DELETE request

Delete request with confirmation modal.

```php
document.querySelector('#delete-client-btn')?.addEventListener('click', () => {
    let title = 'Are you sure that you want to delete this client?';
    createAlertModal(title, '', () => {
        submitDelete(`clients/${clientId}`, true).then(() => {
            location.href = `clients/list`;
        });
    });
});
```

#### `submitDelete()` in `public/assets/general/ajax/submit-delete-request.js`
```js
/**
 * Send DELETE request.
 *
 * @param {string} route after base path
 * @param {boolean|string} redirectToRouteIfUnauthenticated true or redirect route url after base path.
 * If true, the redirect url is the same as the given route
 * @return Promise with as content server response as JSON
 */
export function submitDelete(route, redirectToRouteIfUnauthenticated = false) {
    // ...
}
```


### Submit new data: POST request

Currently, the application only submits new values through modal forms. The logic is a bit more than just a simple
POST request. It retrieves the form data with the html id, checks the validity, disables the form fields during the
request and closes the modal box on success.

Submit modal form with flash message and client list reload.

```js
submitModalForm('create-client-modal-form', 'clients', 'POST')?.then(() => {
    displayFlashMessage('success', translated['Client created successfully.']);
    fetchAndLoadClients();
})
```

#### `submitModalForm()` in `public/assets/general/page-component/modal/modal-submit-request.js`

```js
/**
 * Retrieves form data, checks form validity, disables form, submits modal form and closes it on success
 *
 * @param {string} modalFormId
 * @param {string} moduleRoute POST module route like "users" or "clients"
 * @param {string} httpMethod POST or PUT
 * @param {boolean|string} redirectToRouteIfUnauthenticated true or redirect route url after base path.
 * If true, the redirect url is the same as the given route.
 * @return void|Promise
 */
function submitModalForm(modalFormId, moduleRoute, httpMethod = 'POST', redirectToRouteIfUnauthenticated = false) {
    // ...
}
```

</details>

<details>
  <summary><h2>Translations in JS modules</h2></summary>

Translations are done in the backend by PHP `gettext()` function. 

To access them via Javascript we can make an Ajax request that loads in the background while the page loads. 
This obviously adds a delay for the availability of the translated words so this method should only be used 
with "secondary" things that are not visible on the page load. It works for things like modal boxes that 
are displayed only after a user action is being made. There is most probably enough time for the Ajax request
to be done loading before the content is needed.

Another aspect to take into account is that the `.po` editor fetches only the strings that are an argument 
for the function `__()` to be translated meaning it's enough to call the dummy function `__()` with the 
strings for them to be recognized by the editor.    
If not done already, the public dir has to be added as a source path in Poedit: 
`Translation -> Properties -> Source paths -> add -> public/`.  
Now the button "Update from code" should work.

```js
import {__} from "../../general-js/functions.js";
import {fetchTranslations} from "../../ajax/fetch-translation-data.js";

// List of words that are used in modal box and need to be translated
let wordsToTranslate = [
    __('Change password'),
    __('Old password'),
    __('New password'),
    __('Repeat new password'),
];
// Init translated var by populating it with english values as a default so that all keys are existing
let translated = Object.fromEntries(wordsToTranslate.map(value => [value, value]));
// Fetch translations and replace translated var
fetchTranslations(wordsToTranslate).then(response => {
    // Fill the var with a JSON of the translated words. Key is the original english words and value the translated one
    translated = response;
});
```
#### Usage
```js
export function displayUserCreateModal() {
    // Using translated string "Change password"
    let header = `<h2>${translated['Change password']}</h2>`;
    // ...
}
```

After adding a new string that calls the function `__()`, the string has to be translated to all available
languages in Poedit (obviously).

</details>