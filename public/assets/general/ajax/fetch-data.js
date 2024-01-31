import {basePath} from "../general-js/config.js?v=0.4.0";
import {handleFail} from "./ajax-util/fail-handler.js?v=0.4.0";

/**
 * Sends a GET request and returns result in promise
 *
 * @param {string} route the part after base path (e.g. 'users/1'). Query params have to be added with ?param=value
 * @return {Promise<JSON>}
 */
export function fetchData(route) {
    return fetch(basePath + route, {method: 'GET', headers: {"Content-type": "application/json"}})
        .then(async response => {
            if (!response.ok) {
                await handleFail(response);
                throw response;
            }
            return response.json();
        });
    // Without catch block to let the calling function implement it
}
