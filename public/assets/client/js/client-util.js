import {basePath} from "../../general/js/config.js";

/**
 * @return {object}
 */
export function loadClientDropdownOptions(callbackFunction) {
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
                let response = JSON.parse(xHttp.responseText);
                if (response.hasOwnProperty('users') && response.hasOwnProperty('statuses')){
                    callbackFunction(response);
                    return response;
                }
            }
        }
    };

    // For GET requests, query params have to be passed in the url directly. They are ignored in send()
    xHttp.open('GET', basePath + 'clients/dropdown-options', true);
    xHttp.setRequestHeader("Content-type", "application/json");

    xHttp.send();
}