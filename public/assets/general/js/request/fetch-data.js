import {handleFail} from "../requestUtil/fail-handler.js";
import {basePath} from "../config.js";

/**
 * Sends a GET request
 *
 * @param {string} route without basepath and trailing slash
 * @param {string} queryParams question mark has to be included
 * @return {Promise<JSON>}
 */
export function fetchData(route, queryParams = '') {
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
        xHttp.open('GET', basePath + route + queryParams, true);
        xHttp.setRequestHeader("Content-type", "application/json");

        xHttp.send();
    });
}
