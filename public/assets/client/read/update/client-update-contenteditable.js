import {removeValidationErrorMessages} from "../../../general/ajax/ajax-util/fail-handler.js?v=0.2.0";
import {
    addIconToAvailableDiv,
    removeIconFromAvailableDiv,
    showPersonalInfoContainerIfHidden
} from "../client-read-personal-info.js?v=0.2.0";
import {
    contentEditableFieldValueIsValid,
    disableEditableField,
    makeFieldEditable
} from "../../../general/page-component/contenteditable/contenteditable-main.js?v=0.2.0";
import {submitUpdate} from "../../../general/ajax/submit-update-data.js?v=0.2.0";

function preventLinkOpening(e) {
    /* Prevent link from being opened */
    e.preventDefault();
}

/**
 * Make text value as editable and attach event listeners
 */
export function makeClientFieldEditable() {
    let personalInfoContainer = document.querySelector('#client-personal-info-flex-container');
    let fieldContainer = this.parentNode;
    let fieldElement = fieldContainer.dataset.fieldElement;
    let field = this.parentNode.querySelector(fieldElement);

    // Show personal info container if hidden because it was previously empty
    showPersonalInfoContainerIfHidden();

    // Lock min-width for the container to not shrink during editing
    personalInfoContainer.style.minWidth = personalInfoContainer.offsetWidth + 'px';

    // Field element is usually the field element but there are special cases like when the parent is <a> and content span
    if (fieldElement === 'a-span') {
        field = fieldContainer.querySelector('span');
        let a = fieldContainer.closest('a');
        // Add class to prevent :focus css rule. It is removed in saveClientValue()
        a.classList.add('currently-editable');
        // Add event listener that prevents the link opening in direct function call as anonymous functions can't be removed
        a.addEventListener('click', preventLinkOpening);
    }

    // Remove age addition from birthdate span to edit the date
    if (field.dataset.name === 'birthdate') {
        field.querySelector('#age-sub-span')?.remove();
    }

    makeFieldEditable(field);

    // Save btn event listener is not needed as by clicking on the button the focus goes out of the edited field
    field.addEventListener('focusout', validateContentEditableAndSaveClientValue);
    // Add event listener on email in
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
    // Disable contenteditable on field and remove save icon
    disableEditableField(field);

    let fieldContainer = field.parentNode;
    let submitValue = field.textContent.trim();

    let clientId = document.getElementById('client-id').value;
    submitUpdate(
        {[field.dataset.name]: submitValue},
        `clients/${clientId}`,
        `clients/${clientId}`
    ).then(responseJson => {
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
                // Search upwards the closest span
                let a = fieldContainer.closest('a');
                a.classList.remove('currently-editable');
                a.removeEventListener('click', preventLinkOpening);
            }

            // Hide icon if it existed in the available personal info icon container
            if (fieldContainer.dataset.hideIfEmpty === 'true' && availableIcon !== null) {
                removeIconFromAvailableDiv(availableIcon);
            }

            // Do actions after specific field changes. At this point its certain that values are not empty
            // Add age to birthdate if
            if (field.dataset.name === 'birthdate') {
                // If birthdate field and not empty, add span with age
                field.insertAdjacentHTML(
                    'beforeend',
                    `<span id="age-sub-span">&nbsp; •&nbsp; ${responseJson.data.age}</span>`
                );
            }
            // Add email to link when field changed
            if (field.dataset.name === 'email') {
                field.closest('a').href = `mailto:${submitValue}`;
            }
            // Add phone number to link when field changed
            if (field.dataset.name === 'phone') {
                field.closest('a').href = `tel:${submitValue}`;
            }
        }
    }).catch(responseJson => {
        // If request not successful, make field editable again
        makeClientFieldEditable.call(field);
    });
}