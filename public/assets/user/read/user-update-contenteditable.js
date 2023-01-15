import {removeValidationErrorMessages} from "../../general/ajax/ajax-util/fail-handler.js?v=0.2.0";
import {
    contentEditableFieldValueIsValid,
    disableEditableField,
    makeFieldEditable
} from "../../general/page-component/contenteditable/contenteditable-main.js?v=0.2.0";
import {submitUpdate} from "../../general/ajax/submit-update-data.js?v=0.2.0";

/**
 * Make text value as editable and attach event listeners
 * The functions reassemble client-update-contenteditable but e
 * there are too many module specificities, so some things are duplicate
 */
export function makeUserFieldEditable() {
    // "this" is the edit icon
    let field = this.parentNode.querySelector(this.parentNode.dataset.fieldElement);

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
        removeValidationErrorMessages();

        saveUserValueAndDisableContentEditable(this);
    } else {
        // Re-enable contenteditable if field is invalid in case this function was called after save button press
        this.contentEditable = 'true';
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
    submitUpdate(
        {[field.dataset.name]: field.textContent.trim()},
        `users/${userId}`,
        `users/${userId}`
    ).then(responseJson => {
        // Field disabled before save request and re enabled on error
    }).catch(responseJson => {
        // If request not successful, keep field editable and focus it
        makeFieldEditable(field);
    });
}

