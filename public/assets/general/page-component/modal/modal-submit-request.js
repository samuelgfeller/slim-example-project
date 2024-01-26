import {getFormData, toggleEnableDisableForm} from "./modal-form.js?v=0.4.0";
import {handleFail} from "../../ajax/ajax-util/fail-handler.js?v=0.4.0";
import {closeModal} from "./modal.js?v=0.4.0";
import {basePath} from "../../general-js/config.js?v=0.4.0";

/**
 * Retrieves form data, checks form validity, disables form, submits modal form and closes it on success
 *
 * @param {string} modalFormId
 * @param {string} moduleRoute POST module route like "users" or "clients"
 * @param {string} httpMethod POST or PUT
 * @param {boolean|string} redirectToRouteIfUnauthenticated true or redirect route url after base path.
 * If true, the redirect url is the same as the given route.
 * @return void|Promise with as content server response as JSON
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

    let headers = {
        "Content-type": "application/json"
    };

    // If redirectToRouteIfUnauthenticated is true, redirect to the same location otherwise to given route
    if (redirectToRouteIfUnauthenticated === true) {
        headers["Redirect-to-url-if-unauthorized"] = basePath + moduleRoute;
    } else if (typeof redirectToRouteIfUnauthenticated === "string") {
        headers["Redirect-to-url-if-unauthorized"] = basePath + redirectToRouteIfUnauthenticated;
    }

    return fetch(basePath + moduleRoute, {
        method: httpMethod,
        headers: headers,
        body: JSON.stringify(formData)
    })
        .then(async response => {
            if (!response.ok) {
                // Re enable form if request is not successful
                toggleEnableDisableForm(modalFormId);
                // Default fail handler
                await handleFail(response);
                // Throw error so it can be caught in catch block
                throw new Error('Response status not 2xx. Status: ' + response.status);
            }
            closeModal();
            return response.json();
        });
}