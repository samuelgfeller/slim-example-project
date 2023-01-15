import {basePath} from "../../general-js/config.js?v=0.2.0";
import {handleFail} from "../../ajax/ajax-util/fail-handler.js?v=0.2.0";

/**
 * @param {string} moduleRoute name of the module for the route preceding "/dropdown-options"
 *
 * @return {object}
 */
export function requestDropdownOptions(moduleRoute) {
    return new Promise(function (resolve, reject) {
        let xHttp = new XMLHttpRequest();
        xHttp.onreadystatechange = function () {
            if (xHttp.readyState === XMLHttpRequest.DONE) {
                // Fail
                if (xHttp.status !== 200) {
                    // Default fail handler
                    handleFail(xHttp);
                    // reject() only needed if promise is caught with .catch()
                }
                // Success
                else {
                    let response = JSON.parse(xHttp.responseText);
                    resolve(response);
                }
            }
        };

        // For GET requests, query params have to be passed in the url directly. They are ignored in send()
        xHttp.open('GET', basePath + moduleRoute + '/dropdown-options', true);
        xHttp.setRequestHeader("Content-type", "application/json");

        xHttp.send();
    });
}