import {displayFormInputErrorMessage} from "../validation/form-validation.js";
import {createFlashMessage} from "./flash-message.js";

/**
 * If a request fails this function can be called which gives the user
 * information about which error it is
 *
 * @param {XMLHttpRequest} xhr
 */
export function handleFail(xhr) {
    // Example: 404 Not Found
    let errorMsg = xhr.status + ' ' + xhr.statusText;

    if (xhr.status === 401) {
        // Overwriting general error message to unauthorized
        errorMsg += '<br>Access denied please log in and try again.';
        let responseData = JSON.parse(xhr.responseText);
        // If login url is provided by the server, redirect client to it
        if (responseData.hasOwnProperty('loginUrl') && responseData.loginUrl !== '') {
            window.location.href = responseData.loginUrl;
        }
    }

    if (xhr.status === 403) {
        errorMsg += '<br>Forbidden. You do not have access to this area or function';
    }

    if (xhr.status === 500) {
        errorMsg += '<br>Please try again and then <a href="mailto:contact@samuel-gfeller.ch">contact me</a>.';
    }

// If validation error ignore the default message and create specific one
    if (xhr.status === 422) {
        if (xhr.getResponseHeader('Content-type') === 'application/json') {
            errorMsg = '';
            let json = xhr.response;
            let validationResponse = JSON.parse(json);
            // Remove any existing previous error messages as this is the result of a new request
            for (const errorMsg of document.querySelectorAll('strong.err-msg')){
                errorMsg?.remove();
            }
            // Remove the classname invalid-input on any element that had it
            for (const elementWithInvalidInput of document.querySelectorAll('.invalid-input')){
                elementWithInvalidInput.classList.remove('invalid-input');
            }
            // Best foreach loop method according to https://stackoverflow.com/a/9329476/9013718
            for (const error of validationResponse.data.errors) {
                displayFormInputErrorMessage(error.field, error.message);
                // Flash error message with details
                errorMsg += error.message + ' for <b>' + error.field.replace(/[^a-zA-Z0-9 ]/g, ' ') + '</b><br>';
            }
        } else {
            // Default error message when server returns 422 but not json
            errorMsg = 'Validation error. Something could not have been validate on the server.';
        }
    }

// Output error to user
    createFlashMessage('error', errorMsg);
}