import {getFormData, toggleEnableDisableForm} from "./modal-form.js?v=0.2.0";
import {handleFail} from "../../ajax/ajax-util/fail-handler.js?v=0.2.0";
import {closeModal} from "./modal.js?v=0.2.0";
import {basePath} from "../../general-js/config.js?v=0.2.0";

/**
 * Check form validity, disable form, submit modal form and close it on success
 *
 * @param {string} modalFormId
 * @param {string} moduleRoute POST module route like "users" or "clients"
 * @param {string} httpMethod POST or PUT
 * @param {boolean|string} redirectToRouteIfUnauthenticated true or redirect route url after base path.
 * If true, the redirect url is the same as the given route. If redirect route is the same as the
 * location of the page the user was when submitting the modal form, it's not needed. Example: "users/1"
 * @return void|Promise
 */
export function submitModalForm(
    modalFormId, moduleRoute, httpMethod, redirectToRouteIfUnauthenticated = false
) {
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
                    resolve(JSON.parse(xHttp.responseText));
                }
            }
        };

        xHttp.open(httpMethod, basePath + moduleRoute, true);
        xHttp.setRequestHeader("Content-type", "application/json");

        if (redirectToRouteIfUnauthenticated === true) {
            xHttp.setRequestHeader("Redirect-to-url-if-unauthorized", basePath + route);
        } else if (typeof redirectToRouteIfUnauthenticated === "string") {
            xHttp.setRequestHeader("Redirect-to-url-if-unauthorized", basePath + redirectToRouteIfUnauthenticated);
        }
        // Data format: "fname=Henry&lname=Ford"
        xHttp.send(JSON.stringify(formData));
    });
}