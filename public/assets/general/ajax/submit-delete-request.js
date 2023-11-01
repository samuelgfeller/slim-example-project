import {basePath} from "../general-js/config.js?v=0.4.0";
import {handleFail} from "./ajax-util/fail-handler.js?v=0.4.0";


/**
 * Send DELETE request.
 *
 * @param {string} route after base path
 * @param {boolean|string} redirectToRouteIfUnauthenticated true or redirect route url after base path.
 * If true, the redirect url is the same as the given route
 * @return Promise with as content server response as JSON
 */
export function submitDelete(route, redirectToRouteIfUnauthenticated = false) {
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

        if (redirectToRouteIfUnauthenticated === true) {
            xHttp.setRequestHeader("Redirect-to-url-if-unauthorized", basePath + route);
        } else if (typeof redirectToRouteIfUnauthenticated === "string") {
            xHttp.setRequestHeader("Redirect-to-url-if-unauthorized", basePath + redirectToRouteIfUnauthenticated);
        }

        xHttp.send();
    });
}
