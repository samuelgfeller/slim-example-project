import {basePath} from "../../../general/js/config.js";
import {loadClients} from "../list/client-list-loading.js";
import {handleFail} from "../../../general/js/requests/fail-handler.js";

/**
 * Send client creation to server
 */
export function submitCreateClient() {
    // Check if form content is valid (frontend validation)
    let createForm = document.getElementById('create-client-modal-form');
    if (createForm.checkValidity() === false) {
        // If not valid, report to user and return void
        createForm.reportValidity();
        return;
    }
    // Check that either firstname or last name is set
    let firstNameInp = createForm.querySelector('#first-name-input');
    let lastNameInp = createForm.querySelector('#last-name-input');
    if (firstNameInp.value === '' && lastNameInp.value === ''){
        firstNameInp.style.borderBottom = '2px solid #c0000a';
        confirm('Please fill out either the first name or last name');
        return;
    }

    // Serialize form data before disabling form elements
    let formData = getFormData(createForm);
    // Disable form to indicate that the request is made AFTER getting form data as FormData doesn't consider disabled fields
    toggleDisableForm('create-client-modal-form');

    let xHttp = new XMLHttpRequest();
    xHttp.onreadystatechange = function () {
        if (xHttp.readyState === XMLHttpRequest.DONE) {
            // Fail
            if (xHttp.status !== 201 && xHttp.status !== 200) {
                // Re enable form if request is not successful
                toggleDisableForm('create-client-modal-form');
                // Default fail handler
                handleFail(xHttp);
            }
            // Success
            else {
                closeModal();
                loadClients();
            }
        }
    };

    xHttp.open('POST', basePath + 'clients', true);
    xHttp.setRequestHeader("Content-type", "application/json");

    // Data format: "fname=Henry&lname=Ford"
    xHttp.send(JSON.stringify(formData));
}

/**
 * Returns form element in object where key is the element name
 * and value the element value.
 * Source https://gomakethings.com/how-to-serialize-form-data-with-vanilla-js/
 *
 * @param form Form element
 * @return {{}}
 */
function getFormData(form){
    let formObject = {};
    let formData = new FormData(form);
    for (let [key, value] of formData){
        if (value === ''){
            value = null;
        }
        formObject[key] = value;
    }
    return formObject;
}

function toggleDisableForm(formId) {
    formId = '#' + formId;
    // Select all inputs, textareas and select fields from form
    let formElements = document.querySelectorAll(formId + ' input, ' + formId + ' textarea,' + formId + ' select');
    // Opposite of disabled state of first form element
    let disabledState = !formElements[0].disabled;
    for (let formElement of formElements) {
        formElement.disabled = disabledState;
    }
}