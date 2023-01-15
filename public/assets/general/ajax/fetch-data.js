import {handleFail} from "./ajax-util/fail-handler.js?v=0.2.0";
import {basePath} from "../general-js/config.js?v=0.2.0";

/**
 * Sends a GET request and returns result in promise
 *
 * @param {string} route without base path and trailing slash. Query params have to be added with ?param=value
 * @param {boolean|string} redirectToRouteIfUnauthenticated true or redirect route url after base path.
 * If true, the redirect url is the same as the given route
 * @return {Promise<JSON>}
 */
export function fetchData(route, redirectToRouteIfUnauthenticated = false) {
    return new Promise(function (resolve, reject) {
        let xHttp = new XMLHttpRequest();
        xHttp.onreadystatechange = function () {
            if (xHttp.readyState === XMLHttpRequest.DONE) {
                // Fail
                if (xHttp.status !== 200) {
                    // Default fail handler
                    handleFail(xHttp);
                }
                // Success
                else {
                    // Resolve with json response
                    resolve(JSON.parse(xHttp.responseText));
                }
            }
        };

        // For GET requests, query params have to be passed in the url directly. They are ignored in send()
        xHttp.open('GET', basePath + route, true);
        xHttp.setRequestHeader("Content-type", "application/json");

        if (redirectToRouteIfUnauthenticated === true) {
            xHttp.setRequestHeader("Redirect-to-url-if-unauthorized", basePath + route);
        } else if (typeof redirectToRouteIfUnauthenticated === "string") {
            xHttp.setRequestHeader("Redirect-to-url-if-unauthorized", basePath + redirectToRouteIfUnauthenticated);
        }

        xHttp.send();
    });
}
