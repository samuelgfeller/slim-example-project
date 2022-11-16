import {getFormData, toggleEnableDisableForm} from "./modal-form.js";
import {handleFail} from "../requests/fail-handler.js";
import {closeModal} from "./modal.js";
import {basePath} from "../config.js";

/**
 * Check form validity, disable form, submit modal form and close it on success
 *
 * @param {string} modalFormId
 * @param {string} moduleRoute POST module route like "users" or "clients"
 * @param {string} httpMethod POST or PUT
 @param {string|null} redirectUrlIfUnauthenticated url after base path. If redirect route is the same as the
 * location where the user was before submitting the modal form, it's not needed. Example: "users/1"
 * @return void|Promise
 */
export function submitModalForm(modalFormId, moduleRoute, httpMethod, redirectUrlIfUnauthenticated = null) {
    // Check if form content is valid (frontend validation)
    let modalForm = document.getElementById(modalFormId);
    if (modalForm.checkValidity() === false) {
        // If not valid, report to user and return void
        modalForm.reportValidity();
        // If nothing is returned "then()" will not exist; add "?" before the call: submitModalForm()?.then()
        return;
    }

    // Serialize form data before disabling form elements
    let formData = getFormData(modalForm);

    // Disable form to indicate that the request is made AFTER getting form data as FormData doesn't consider disabled fields
    toggleEnableDisableForm(modalFormId);

    return new Promise(function (resolve, reject) {
        let xHttp = new XMLHttpRequest();
        xHttp.onreadystatechange = function () {
            if (xHttp.readyState === XMLHttpRequest.DONE) {
                // Fail
                if (xHttp.status !== 201 && xHttp.status !== 200) {
                    // Re enable form if request is not successful
                    toggleEnableDisableForm(modalFormId);
                    // Default fail handler
                    handleFail(xHttp);
                    // reject() only needed if promise is caught with .catch()
                }
                // Success
                else {
                    closeModal();
                    resolve();
                }
            }
        };

        xHttp.open(httpMethod, basePath + moduleRoute, true);
        xHttp.setRequestHeader("Content-type", "application/json");
        if (redirectUrlIfUnauthenticated !== null) {
            xHttp.setRequestHeader("Redirect-to-url-if-unauthorized", basePath + redirectUrlIfUnauthenticated);
        }
        // Data format: "fname=Henry&lname=Ford"
        xHttp.send(JSON.stringify(formData));
    });
}