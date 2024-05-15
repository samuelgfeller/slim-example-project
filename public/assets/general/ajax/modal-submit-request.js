import {getFormData, toggleEnableDisableForm} from "../page-component/modal/modal-form.js?v=1.0.0";
import {basePath} from "../general-js/config.js?v=1.0.0";
import {handleFail} from "./ajax-util/fail-handler.js?v=1.0.0";
import {closeModal} from "../page-component/modal/modal.js?v=1.0.0";

/**
 * Retrieves form data, checks form validity, disables form, submits modal form and closes it on success.
 *
 * @param {string} modalFormId
 * @param {string} moduleRoute POST module route like "users" or "clients"
 * @param {string} httpMethod POST or PUT
 * @return {void|Promise} with as content server response as JSON
 */
export function submitModalForm(
    modalFormId, moduleRoute, httpMethod
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

    return fetch(basePath + moduleRoute, {
        method: httpMethod,
        headers: {"Content-type": "application/json", "Accept": "application/json"},
        body: JSON.stringify(formData)
    })
        .then(async response => {
            if (!response.ok) {
                // Re enable form if request is not successful
                toggleEnableDisableForm(modalFormId);
                // Default fail handler
                await handleFail(response);
                // Throw error so it can be caught in catch block
                throw new Error('Response status: ' + response.status);
            }
            closeModal();
            return response.json();
        });
}