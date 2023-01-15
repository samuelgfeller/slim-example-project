import {displayValidationErrorMessage} from "../../validation/form-validation.js?v=0.2.0";
import {displayFlashMessage} from "../../page-component/flash-message/flash-message.js?v=0.2.0";

/**
 * If a request fails this function can be called which gives the user
 * information about which error it is
 *
 * @param {XMLHttpRequest} xhr
 * @param {null|string} domFieldId css id of dom field the fail is about
 */
export function handleFail(xhr, domFieldId = null) {
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
        errorMsg += '<br>Forbidden. Not allowed to access this area or function.';
    }

    if (xhr.status === 500) {
        errorMsg += '<br>Please try again and then <a href="mailto:contact@samuel-gfeller.ch">contact me</a>.';
    }

    // If validation error ignore the default message and create specific one
    let noFlashMessage = false;
    if (xhr.status === 422) {
        if (xhr.getResponseHeader('Content-type') === 'application/json') {
            errorMsg = '';
            let json = xhr.response;
            let validationResponse = JSON.parse(json);
            removeValidationErrorMessages();
            // Best foreach loop method according to https://stackoverflow.com/a/9329476/9013718
            for (const error of validationResponse.data.errors) {
                displayValidationErrorMessage(error.field, error.message, domFieldId);
                // Flash error message with details
                errorMsg += error.message + '.<br>Field "<b>' + error.field.replace(/[^a-zA-Z0-9 ]/g, ' ')
                    + '</b>".<br>';
            }
            noFlashMessage = true;
        } else {
            // Default error message when server returns 422 but not json
            errorMsg = 'Validation error. Something could not have been validate on the server.';
        }
    }

    // Output error to user
    if (noFlashMessage === false) {
        displayFlashMessage('error', errorMsg);
    }
}

/**
 * Removes any validation message and invalid-input class names
 */
export function removeValidationErrorMessages() {
// Remove any existing previous error messages as this is the result of a new request
    for (const errorMsg of document.querySelectorAll('strong.err-msg')) {
        errorMsg?.remove();
    }
    // Remove the classname invalid-input on any element that had it
    for (const elementWithInvalidInput of document.querySelectorAll('.invalid-input')) {
        elementWithInvalidInput.classList.remove('invalid-input');
    }
}

