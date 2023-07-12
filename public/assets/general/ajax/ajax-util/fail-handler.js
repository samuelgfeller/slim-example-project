import {displayValidationErrorMessage} from "../../validation/form-validation.js?v=0.4.0";
import {displayFlashMessage} from "../../page-component/flash-message/flash-message.js?v=0.4.0";
import {__} from "../../general-js/functions.js?v=0.4.0";
import {fetchTranslations} from "../fetch-translation-data.js?v=0.4.0";

// List of words that are used in modal box and need to be translated
let wordsToTranslate = [
    __('Access denied please log in and try again'),
    __('Forbidden. Not allowed to access this area or function'),
    __('Please try again and report the error to an administrator'),
];
// Init translated var by populating it with english values as a default so that all keys are surely existing
let translated = Object.fromEntries(wordsToTranslate.map(value => [value, value]));
// Fetch translations and replace translated var
fetchTranslations(wordsToTranslate).then(response => {
    // Fill the var with a JSON of the translated words. Key is the original english words and value the translated one
    translated = response;
});

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
        errorMsg += `<br>${translated['Access denied please log in and try again']}.`;
        let responseData = JSON.parse(xhr.responseText);
        // If login url is provided by the server, redirect client to it
        if (responseData.hasOwnProperty('loginUrl') && responseData.loginUrl !== '') {
            window.location.href = responseData.loginUrl;
        }
    }

    if (xhr.status === 403) {
        errorMsg += `<br>${translated['Forbidden. Not allowed to access this area or function']}.`;
    }

    if (xhr.status === 500) {
        errorMsg += `<br>${translated['Please try again and report the error to an administrator']}.`;
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

