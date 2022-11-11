import {submitClientUpdate} from "./client-update-request.js";
import {removeValidationErrorMessages} from "../../../../general/js/requests/fail-handler.js";
import {addIconToAvailableDiv, removeIconFromAvailableDiv} from "../client-read-personal-info.js";
import {
    contentEditableFieldValueIsValid,
    disableEditableField,
    makeFieldEditable
} from "../../../../general/js/contenteditable/contenteditable-main.js";

function preventLinkOpening(e) {
    /* Prevent link from being opened */
    e.preventDefault();
}

/**
 * Make text value as editable and attach event listeners
 */
export function makeClientFieldEditable() {
    let fieldContainer = this.parentNode;
    let fieldElement = fieldContainer.dataset.fieldElement;
    let field = this.parentNode.querySelector(fieldElement);

    // Field element is usually the field element but there are special cases like when the parent is <a> and content span
    if (fieldElement === 'a-span') {
        field = fieldContainer.querySelector('span');
        let a = fieldContainer.closest('a');
        // Add class to prevent :focus css rule. It is removed in saveClientValue()
        a.classList.add('currently-editable');
        // Add event listener that prevents the link opening in direct function call as anonymous functions can't be removed
        a.addEventListener('click', preventLinkOpening);
    }
    // Lock min-width for the container to not shrink during editing
    let personalInfoContainer = document.querySelector('#client-personal-info-flex-container');
    personalInfoContainer.style.minWidth = personalInfoContainer.offsetWidth + 'px';
    // Remove age addition from birthdate span to edit the date
    if (field.dataset.name === 'birthdate') {
        field.querySelector('#age-sub-span')?.remove();
    }

    makeFieldEditable(field);

    // Save btn event listener is not needed as by clicking on the button the focus goes out of the edited field
    field.addEventListener('focusout', validateContentEditableAndSaveClientValue);
}

/**
 * Validate frontend, disable contenteditable and make
 * update request.
 */
function validateContentEditableAndSaveClientValue() {
    // "this" is the field
    if (contentEditableFieldValueIsValid(this)) {
        removeValidationErrorMessages();

        saveClientValueAndDisableContentEditable(this);
    } else {
        // Re-enable contenteditable if field is invalid in case this function was called after save button press
        this.contentEditable = 'true';
        // No idea why but contenteditable stays false if the focus is not made here
        // It has an additional benefit of locking the focus on the field until the input is valid
        this.focus();
    }
}

/**
 * Make field non-editable and make call function that
 * makes client update request
 */
function saveClientValueAndDisableContentEditable(field) {
    let fieldContainer = field.parentNode;
    let submitValue = field.textContent.trim();
    // If submit unsuccessful the field focus should not get away
    let clientUpdateRequestPromise = submitClientUpdate(field.dataset.name, submitValue);
    clientUpdateRequestPromise.then(successData => {
        if (successData.success === true) {
            // Reset min width of personal info container
            document.querySelector('#client-personal-info-flex-container').style.minWidth = null;
            let availableIcon = document.querySelector('#add-client-personal-info-div img[alt="' + field.dataset.name + '"]');
            // If success true and submit value was empty string, remove it from client personal infos box but not if header
            if ((submitValue === '' || submitValue === 'NULL') && fieldContainer.dataset.hideIfEmpty === 'true') {
                // Select dropdown container hidden in client-update-dropdown.js
                addIconToAvailableDiv(availableIcon, fieldContainer.parentNode)
            } else {
                // Remove event listener that prevented the link (parent of span) from opening
                if (fieldContainer.dataset.fieldElement === 'a-span') {
                    let a = fieldContainer.closest('a');
                    a.classList.remove('currently-editable');
                    a.removeEventListener('click', preventLinkOpening);
                }

                // Disable contenteditable on field and remove save icon
                disableEditableField(field);

                if (field.dataset.name === 'birthdate') {
                    // If birthdate field and not empty, add span with age
                    field.insertAdjacentHTML('beforeend', `<span id="age-sub-span">&nbsp; •&nbsp; ${successData.data.age}</span>`);
                }
                // Hide icon if it existed in the available personal info icon container
                if (fieldContainer.dataset.hideIfEmpty === 'true' && availableIcon !== null) {
                    removeIconFromAvailableDiv(availableIcon);
                }
            }
        } else {
            // If request not successful,
            field.contentEditable = 'true';
            field.focus();
        }
    });
}