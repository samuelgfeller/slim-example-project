import {basePath} from "../config.js?v=0.1";
import {handleFail, removeValidationErrorMessages} from "../requestUtil/fail-handler.js?v=0.1";


/**
 * Send PUT update request.
 * Fail handled by handleFail() method which supports forms
 * On success validation errors are removed and response content returned
 *
 * @param {object} formFieldsAndValues {field: value} e.g. {[input.name]: input.value}
 * @param {string} route after base path
 * @param {null|string} redirectUrlIfUnauthenticated route after base path
 *
 * @return Promise with as content server response as JSON
 */
export function submitUpdate(formFieldsAndValues, route, redirectUrlIfUnauthenticated = null) {
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
        if (redirectUrlIfUnauthenticated !== null) {
            xHttp.setRequestHeader("Redirect-to-url-if-unauthorized", basePath + redirectUrlIfUnauthenticated);
        }

        // Data format: "fname=Henry&lname=Ford"
        xHttp.send(JSON.stringify(formFieldsAndValues));

    });
}
