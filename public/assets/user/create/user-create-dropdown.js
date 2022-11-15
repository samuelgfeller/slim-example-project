import {basePath} from "../../general/js/config.js";
import {handleFail} from "../../general/js/requests/fail-handler.js";

/**
 *
 * @return {object}
 */
export function requestUserDropdownOptions() {
    return new Promise(function (resolve, reject) {
        let xHttp = new XMLHttpRequest();
        xHttp.onreadystatechange = function () {
            if (xHttp.readyState === XMLHttpRequest.DONE) {
                // Fail
                if (xHttp.status !== 200) {
                    // Default fail handler
                    handleFail(xHttp);
                    reject();
                }
                // Success
                else {
                    let response = JSON.parse(xHttp.responseText);
                    if (response.hasOwnProperty('users') && response.hasOwnProperty('statuses')) {
                        resolve(response);
                        // callbackFunction(response);
                        // return response;
                    }
                }
            }
        };

        // For GET requests, query params have to be passed in the url directly. They are ignored in send()
        xHttp.open('GET', basePath + 'users/dropdown-options', true);
        xHttp.setRequestHeader("Content-type", "application/json");

        xHttp.send();
    });
}