import {basePath} from "../../general-js/config.js?v=0.4.0";
import {handleFail} from "../../ajax/ajax-util/fail-handler.js?v=0.4.0";

/**
 * This function is used to request dropdown options from a specific module route.
 *
 * @param {string} moduleRoute - The name of the module for the route preceding "/dropdown-options".
 *
 * @returns {Promise} - A Promise that resolves to the response of the fetch request.
 * If the response is not "ok", it will call the `handleFail`
 * function with the response as an argument and throw an error.
 * If the response is "ok", it will return the response as a JSON object.
 */
export function requestDropdownOptions(moduleRoute) {
    let headers = {
        "Content-type": "application/json"
    };

    return fetch(basePath + moduleRoute + '/dropdown-options', {
        method: 'GET',
        headers: headers
    }).then(async response => {
        if (!response.ok) {
            await handleFail(response);
            throw new Error('Response was not "ok"');
        }
        return response.json();
    });
}