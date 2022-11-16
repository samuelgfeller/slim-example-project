import {handleFail, removeValidationErrorMessages} from "../../general/js/requests/fail-handler.js";
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
                    console.log(xHttp.responseText);
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
        let userId = document.getElementById('user-id').value;
        let updateRoute = 'users';
        // Password change request has an own action class as there are fields such as password2 and old password
        if ('password' in formFieldsAndValues) {
            updateRoute = 'change-password';
        }
        xHttp.open('PUT', basePath + updateRoute + '/' + userId, true);
        xHttp.setRequestHeader("Content-type", "application/json");
        xHttp.setRequestHeader("Redirect-to-url-if-unauthorized", basePath + "users/" + userId);

        // Data format: "fname=Henry&lname=Ford"
        // In square brackets to be evaluated
        xHttp.send(JSON.stringify(formFieldsAndValues));

    });
}
