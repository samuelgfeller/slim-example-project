import {handleFail} from "../requestUtil/fail-handler.js?v=0.1";
import {basePath} from "../config.js?v=0.1";

/**
 * Sends a GET request
 *
 * @param {string} route without basepath and trailing slash. Query params have to be added with ?param=value
 * @param {string|null} redirectUrlIfUnauthenticated
 * @return {Promise<JSON>}
 */
export function fetchData(route, redirectUrlIfUnauthenticated = null) {
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
        if (redirectUrlIfUnauthenticated !== null) {
            xHttp.setRequestHeader("Redirect-to-url-if-unauthorized", basePath + redirectUrlIfUnauthenticated);
        }

        xHttp.send();
    });
}
