import {handleFail, removeValidationErrorMessages} from "../../general/js/requestUtil/fail-handler.js";
import {basePath} from "../../general/js/config.js";

/**
 * Send user update request
 *
 * @param {object} formFieldsAndValues {"field": value}
 * @return Promise true on success otherwise false
 */
export function submitUserUpdate(formFieldsAndValues) {
    return new Promise(function (resolve, reject) {
        // Make ajax call
        let xHttp = new XMLHttpRequest();
        xHttp.onreadystatechange = function () {
            if (xHttp.readyState === XMLHttpRequest.DONE) {
                // Fail
                if (xHttp.status !== 201 && xHttp.status !== 200) {
                    // Default fail handler
                    handleFail(xHttp);
                    resolve(false);
                    // reject() only needed if promise is caught with .catch()
                }
                // Success
                else {
                    // Remove previous validation messages
                    removeValidationErrorMessages();
                    console.log(xHttp.responseText);
                    // for (const [fieldName, value] of formFieldsAndValues) {
                    //     createFlashMessage('success', fieldName.replace(/_/g, ' ') + ' was updated.');
                    // }
                    // resolve with object containing success and data
                    resolve(true);
                }
            }
        };
        xHttp.open('PUT', basePath + 'users/' + userId, true);
        xHttp.setRequestHeader("Content-type", "application/json");
        xHttp.setRequestHeader("Redirect-to-url-if-unauthorized", basePath + "users/" + userId);

        // Data format: "fname=Henry&lname=Ford"
        // In square brackets to be evaluated
        xHttp.send(JSON.stringify(formFieldsAndValues));

    });
}
