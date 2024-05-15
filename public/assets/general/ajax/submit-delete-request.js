import {basePath} from "../general-js/config.js?v=1.0.0";
import {handleFail} from "./ajax-util/fail-handler.js?v=1.0.0";


/**
 * Send DELETE request.
 *
 * @param {string} route after base path (e.g. 'users/1')
 * @return {Promise} with as content server response as JSON
 */
export function submitDelete(route) {
    return fetch(basePath + route, {
        method: 'DELETE',
        headers: {"Content-type": "application/json", "Accept": "application/json"}
    })
        .then(async response => {
            if (!response.ok) {
                await handleFail(response);
                // Throw error so it can be caught in catch block
                throw new Error('Response status: ' + response.status);
            }
            return response.json();
        });
}
