import {removeValidationErrorMessages} from "../../general/js/requestUtil/fail-handler.js";
import {
    contentEditableFieldValueIsValid,
    disableEditableField,
    makeFieldEditable
} from "../../general/js/contenteditable/contenteditable-main.js";
import {submitUserUpdate} from "../update/user-update-request.js";

/**
 * Make text value as editable and attach event listeners
 * The functions reassemble client-update-contenteditable but e
 * there are too many module specificities, so some things are duplicate
 */
export function makeUserFieldEditable() {
    let field = this.parentNode.querySelector(this.parentNode.dataset.fieldElement);

    // "this" is the edit icon
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
    // If submit unsuccessful the field focus should not get away
    let userUpdateRequestPromise = submitUserUpdate({[field.dataset.name]: field.textContent.trim()});
    userUpdateRequestPromise.then(success => {
        if (success === true) {
            disableEditableField(field);
        } else {
            // If request not successful,
            field.contentEditable = 'true';
            field.focus();
        }
    });
}

