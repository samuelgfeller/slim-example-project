import {handleFail, removeValidationErrorMessages} from "../../general/js/requests/fail-handler.js";
import {basePath} from "../../general/js/config.js";
import {displayFlashMessage} from "../../general/js/requests/flash-message.js";
import {getFormData, toggleEnableDisableForm} from "../../general/js/modal/modal-form.js";
import {closeModal} from "../../general/js/modal/modal.js";

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
        if ('password' in formFieldsAndValues){
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


/**
 * Send client creation to server
 */
export function submitChangePassword() {
    // Check if form content is valid (frontend validation)
    let modalForm = document.getElementById('change-password-modal-form');
    if (modalForm.checkValidity() === false) {
        // If not valid, report to user and return void
        modalForm.reportValidity();
        return;
    }

    // Serialize form data before disabling form elements
    let formData = getFormData(modalForm);
    // Disable form to indicate that the request is made AFTER getting form data as FormData doesn't consider disabled fields
    toggleEnableDisableForm('change-password-modal-form');

    submitUserUpdate(formData).then(success => {
        if (success === true) {
            closeModal();
            displayFlashMessage('success', 'Successfully changed password.');
        } else{
            // Re enable form if request is not successful
            toggleEnableDisableForm('change-password-modal-form');
        }
    });

}

