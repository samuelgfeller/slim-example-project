import {basePath} from "../config.js?v=0.1";
import {handleFail} from "../requestUtil/fail-handler.js?v=0.1";


/**
 * Send PUT update request.
 * Fail handled by handleFail() method which supports forms
 * On success validation errors are removed and response content returned
 *
 * @param {string} route after base path
 * @param {boolean} redirectToSameUrlIfUnauthenticated if redirect url is the same as the given route
 * @param {null|string} redirectUrlIfUnauthenticated route after base path
 *
 * @return Promise with as content server response as JSON
 */
export function submitDelete(route, redirectToSameUrlIfUnauthenticated = false, redirectUrlIfUnauthenticated = null) {
    return new Promise(function (resolve, reject) {
        // Make ajax call
        let xHttp = new XMLHttpRequest();
        xHttp.onreadystatechange = function () {
            if (xHttp.readyState === XMLHttpRequest.DONE) {
                // Fail
                if (xHttp.status !== 200) {
                    // Default fail handler
                    handleFail(xHttp);
                    // reject() only needed if promise is caught with .catch()
                    reject(JSON.parse(xHttp.responseText));
                }
                // Success
                else {
                    resolve(JSON.parse(xHttp.responseText));
                }
            }
        };

        xHttp.open('DELETE', basePath + route, true);
        xHttp.setRequestHeader("Content-type", "application/json");

        if (redirectToSameUrlIfUnauthenticated === true) {
            xHttp.setRequestHeader("Redirect-to-url-if-unauthorized", basePath + route);
        } else if(redirectUrlIfUnauthenticated !== null) {
            xHttp.setRequestHeader("Redirect-to-url-if-unauthorized", basePath + redirectUrlIfUnauthenticated);
        }

        xHttp.send();
    });
}
