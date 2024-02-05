import {removeValidationErrorMessages} from "../../general/ajax/ajax-util/fail-handler.js?v=0.4.0";
import {
    contentEditableFieldValueIsValid,
    disableEditableField,
    makeFieldEditable
} from "../../general/page-component/contenteditable/contenteditable-main.js?v=0.4.0";
import {submitUpdate} from "../../general/ajax/submit-update-data.js?v=0.4.0";

/**
 * Make text value as editable and attach event listeners
 * The functions reassemble client-update-contenteditable, but
 * with other specificities.
 */
export function makeUserFieldEditable() {
    // "this" is the edit icon or the field itself
    let field = this.parentNode.querySelector(this.parentNode.dataset.fieldElement);

    // Make field editable, add save button and focus it
    makeFieldEditable(field);

    // Save btn event listener is not needed as by clicking on the button the focus goes out of the edited field
    field.addEventListener('focusout', validateContentEditableAndSaveUserValue);
}

/**
 * Validate frontend, disable contenteditable and make
 * update request.
 */
function validateContentEditableAndSaveUserValue() {
    // "this" is the field
    if (contentEditableFieldValueIsValid(this)) {
        // Remove validation error messages if any
        removeValidationErrorMessages();
        // Disable contenteditable and save user value
        saveUserValueAndDisableContentEditable(this);
    } else {
        // Lock the focus on the field until the input is valid
        this.focus();
    }
}

/**
 * Make field non-editable and submit user update request
 */
function saveUserValueAndDisableContentEditable(field) {
    disableEditableField(field);
    let userId = document.getElementById('user-id').value;
    let submitValue = field.textContent.trim();
    // submitValue = submitValue === '' ? null : submitValue;
    console.log(submitValue);

    submitUpdate(
        {[field.dataset.name]: submitValue},
        `users/${userId}`
    ).then(responseJson => {
        // Field disabled before save request and re enabled on error
    }).catch(errorMsg => {
        // If request not successful, make the field editable again and focus it
        makeFieldEditable(field);
    });
}

