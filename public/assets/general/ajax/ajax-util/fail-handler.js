import {displayValidationErrorMessage} from "../../validation/form-validation.js?v=0.4.2";
import {displayFlashMessage} from "../../page-component/flash-message/flash-message.js?v=0.4.2";
import {__} from "../../general-js/functions.js?v=0.4.2";
import {fetchTranslations} from "../fetch-translation-data.js?v=0.4.2";

// List of words that are used in modal box and need to be translated
let wordsToTranslate = [
    __('Access denied, please log in and try again'),
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
 * This function can be called with the Response or a TypeError.
 * If response is TypeError, only a flash message is shown.
 * If response is Response, the status code is checked, and a flash message is shown,
 * or the validation errors are highlighted in the form.
 *
 * @param {Response|TypeError} response
 * @param {null|string} domFieldId css id of dom field the fail is about
 */
export async function handleFail(response, domFieldId = null) {
    // If fetch() response is TypeError (e.g. network error), only a flash message is shown
    if (response instanceof TypeError) {
        displayFlashMessage('error', response.message);
        return;
    }

    // Example: 404 Not Found
    let errorMsg = response.status + ' ' + response.statusText;
    // If response is json, parse it, else get text
    let responseData = response.headers.get('Content-type') === 'application/json'
        ? await response.json() : await response.text();

    // If user wasn't authenticated, the response contains the login url with correct redirect back params
    if (response.status === 401) {

        // If login url is provided by the server, redirect client to it
        if (responseData.hasOwnProperty('loginUrl') && responseData.loginUrl !== '') {
            // window.location.href = responseData.loginUrl;
            // Redirect user to login page with redirect back GET param to the current page
            window.location.href = responseData.loginUrl + '?redirect=' + encodeURIComponent(window.location.href);
        }

        // If response data doesn't contain login url
        errorMsg += `<br>${translated['Access denied, please refresh the page and try again']}.`;
    }

    const statusMessageMap = {
        403: translated['Forbidden. Not allowed to access this area or function'],
        500: translated['Please try again and report the error to an administrator']
    };

    // Check if response status is in the map
    if (statusMessageMap.hasOwnProperty(response.status)) {
        // Set error message according to status code
        errorMsg += `<br>${statusMessageMap[response.status]}.`;
    }

    // Validation error
    if (response.status === 422) {
        errorMsg = handleValidationError(response, responseData, domFieldId, errorMsg);
    }

    // If the server provides error detail message, add it to the error message
    if (responseData.hasOwnProperty('error')) {
        errorMsg += '<br><br><b>Error:</b> ' + responseData.error;
    }

    // Output error to user
    // handleValidationError() may add noFlashMessage to the responseData
    if (!responseData.noFlashMessage) {
        displayFlashMessage('error', errorMsg);
    }
}

function handleValidationError(response, responseData, domFieldId, errorMsg) {
    if (response.headers.get('Content-type') === 'application/json' && responseData?.data?.errors) {
        errorMsg = '';
        const validationErrors = responseData.data.errors;
        removeValidationErrorMessages();
        for (const fieldName in validationErrors) {
            const fieldMessages = validationErrors[fieldName];
            // There may be a case where there are multiple error messages for a single field.
            // If that happens, the previous error message is simply replaced by the newer one, which isn't
            // ideal but acceptable as its so rare and a minor inconvenience to the user. The form would have
            // to be submitted again to get the updated (other) error message (that wasn't visible before).
            displayValidationErrorMessage(fieldName, fieldMessages[0], domFieldId);
            // Flash error message with details
            // errorMsg += fieldMessages[0] + '.<br>Field "<b>' + fieldName.replace(/[^a-zA-Z0-9 ]/g, ' ')
            //     + '</b>".<br>';
        }
        // No flash message if the message is already shown in the form
        responseData.noFlashMessage = true;
    } else {
        errorMsg = 'Validation error. Something could not be validated on the server.';
    }
    return errorMsg;
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
    // Remove the classname input-group-error on any element that had it
    for (const elementWithInputGroupError of document.querySelectorAll('.input-group-error')) {
        elementWithInputGroupError.classList.remove('input-group-error');
    }

    document.querySelector('#form-general-error-msg')?.remove();

}

