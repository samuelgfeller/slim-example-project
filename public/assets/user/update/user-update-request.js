import {handleFail, removeValidationErrorMessages} from "../../general/js/requests/fail-handler.js";
import {basePath} from "../../general/js/config.js";
import {createFlashMessage} from "../../general/js/requests/flash-message.js";

/**
 * Send user update request
 *
 * @param field
 * @param value
 * @return Promise true on success otherwise false
 */
export function submitUserUpdate(field, value) {
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
                }
                // Success
                else {
                    // Remove previous validation messages
                    removeValidationErrorMessages();
                    createFlashMessage('success', field.replace('_', ' ') + ' was updated.');
                    // resolve with object containing success and data
                    resolve(true);
                }
            }
        };
        let userId = document.getElementById('user-id').value;
        xHttp.open('PUT', basePath + 'users' + '/' + userId, true);
        xHttp.setRequestHeader("Content-type", "application/json");
        xHttp.setRequestHeader("Redirect-to-url-if-unauthorized", basePath + "users/" + userId);

        // Data format: "fname=Henry&lname=Ford"
        // In square brackets to be evaluated
        xHttp.send(JSON.stringify({[field]: value}));

    });
}