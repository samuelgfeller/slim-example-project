import {handleFail, removeValidationErrorMessages} from "../../../../general/js/requestUtil/fail-handler.js";
import {basePath} from "../../../../general/js/config.js";

/**
 * Send client update request
 *
 * @param field
 * @param value
 * @return Promise true on success otherwise false
 */
export function submitClientUpdate(field, value) {
    return new Promise(function (resolve, reject) {
        // Make ajax call
        let xHttp = new XMLHttpRequest();
        xHttp.onreadystatechange = function () {
            if (xHttp.readyState === XMLHttpRequest.DONE) {
                // Fail
                if (xHttp.status !== 201 && xHttp.status !== 200) {
                    // Default fail handler
                    handleFail(xHttp);
                    resolve({success: false});
                }
                // Success
                else {
                    // Remove previous validation messages
                    removeValidationErrorMessages();
                    // createFlashMessage('success', field.replace('_', ' ') + ' was updated.');
                    // resolve with object containing success and data like age of birthdate
                    resolve({success: true, data: JSON.parse(xHttp.responseText).data});
                }
            }
        };
        let clientId = document.getElementById('client-id').value;
        xHttp.open('PUT', basePath + 'clients' + '/' + clientId, true);
        xHttp.setRequestHeader("Content-type", "application/json");
        xHttp.setRequestHeader("Redirect-to-url-if-unauthorized", basePath + "clients/" + clientId);

        // Data format: "fname=Henry&lname=Ford"
        // In square brackets to be evaluated
        xHttp.send(JSON.stringify({[field]: value}));

    });
}