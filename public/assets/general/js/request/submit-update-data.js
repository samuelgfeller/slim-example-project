import {basePath} from "../config.js";
import {handleFail} from "../requestUtil/fail-handler.js";


/**
 * Send PUT update request
 *
 * @param {object} formFieldsAndValues {field: value} e.g. {[input.name]: input.value}
 * @param {string} route after base path
 * @param {null|string} redirectUrlIfUnauthenticated route after base path
 *
 * @return Promise true on success otherwise false
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
                }
                // Success
                else {
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
