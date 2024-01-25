import {basePath} from "../general-js/config.js?v=0.4.0";
import {handleFail, removeValidationErrorMessages} from "./ajax-util/fail-handler.js?v=0.4.0";


/**
 * Send PUT update request.
 * Fail handled by handleFail() method that supports forms
 * On success validation errors are removed and response content returned
 *
 * @param {object} formFieldsAndValues {field: value} e.g. {[input.name]: input.value}
 * @param {string} route after base path e.g. clients/1
 * @param {boolean|string} redirectToRouteIfUnauthenticated true or redirect route url after base path.
 * If true, the redirect url is the same as the given route
 * @param domFieldId field id to display the validation error message for the correct field
 * @return Promise with as content server response as JSON
 */
export function submitUpdate(formFieldsAndValues, route, redirectToRouteIfUnauthenticated = false, domFieldId = null) {
    let headers = {
        "Content-type": "application/json"
    };

    // If redirectToRouteIfUnauthenticated is true, redirect to the same route
    if (redirectToRouteIfUnauthenticated === true) {
        headers["Redirect-to-url-if-unauthorized"] = basePath + route;
    } else if (typeof redirectToRouteIfUnauthenticated === "string") {
        // Else if it's a string, redirect to the given route
        headers["Redirect-to-url-if-unauthorized"] = basePath + redirectToRouteIfUnauthenticated;
    }

    return fetch(basePath + route, {
        method: 'PUT',
        headers: headers,
        body: JSON.stringify(formFieldsAndValues)
    })
        .then(async response => {
            if (!response.ok) {
                await handleFail(response, domFieldId);
                throw new Error('Response status not 2xx. Status: ' + response.status);
            }
            // Remove validation error messages if there are any
            removeValidationErrorMessages();
            return response.json();
        });
}