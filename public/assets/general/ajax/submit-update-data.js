import {basePath} from "../general-js/config.js?v=0.2.0";
import {handleFail, removeValidationErrorMessages} from "./ajax-util/fail-handler.js?v=0.2.0";


/**
 * Send PUT update request.
 * Fail handled by handleFail() method which supports forms
 * On success validation errors are removed and response content returned
 *
 * @param {object} formFieldsAndValues {field: value} e.g. {[input.name]: input.value}
 * @param {string} route after base path
 * @param {boolean|string} redirectToRouteIfUnauthenticated true or redirect route url after base path.
 * If true, the redirect url is the same as the given route
 *
 * @return Promise with as content server response as JSON
 */
export function submitUpdate(formFieldsAndValues, route, redirectToRouteIfUnauthenticated = false) {
    return new Promise(function (resolve, reject) {
        // Make ajax call
        let xHttp = new XMLHttpRequest();
        xHttp.onreadystatechange = function () {
            if (xHttp.readyState === XMLHttpRequest.DONE) {
                // Fail
                if (xHttp.status !== 201 && xHttp.status !== 200) {
                    // Default fail handler
                    handleFail(xHttp);
                    // reject() only needed if promise is caught with .catch()
                    reject(JSON.parse(xHttp.responseText));
                }
                // Success
                else {
                    removeValidationErrorMessages();
                    resolve(JSON.parse(xHttp.responseText));
                }
            }
        };

        xHttp.open('PUT', basePath + route, true);
        xHttp.setRequestHeader("Content-type", "application/json");

        if (redirectToRouteIfUnauthenticated === true) {
            xHttp.setRequestHeader("Redirect-to-url-if-unauthorized", basePath + route);
        } else if (typeof redirectToRouteIfUnauthenticated === "string") {
            xHttp.setRequestHeader("Redirect-to-url-if-unauthorized", basePath + redirectToRouteIfUnauthenticated);
        }

        // Data format: "fname=Henry&lname=Ford"
        xHttp.send(JSON.stringify(formFieldsAndValues));

    });
}
