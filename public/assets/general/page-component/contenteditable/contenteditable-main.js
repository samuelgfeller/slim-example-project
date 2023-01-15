import {displayValidationErrorMessage} from "../../validation/form-validation.js?v=0.2.0";

/**
 * Make field value editable
 * "this" is the edit icon
 */
export function makeFieldEditable(field) {
    let editIcon = field.parentNode.querySelector('.contenteditable-edit-icon');
    let fieldContainer = field.parentNode;

    // Hide edit icon, make field editable, focus it and remove &nbsp; if empty
    editIcon.style.display = 'none';
    field.contentEditable = 'true';
    field.focus();
    if (field.innerHTML === '&nbsp;') {
        field.innerHTML = '';
    }

    // Slick would be to replace the word "edit" of the edit icon with "save" for the save button but that puts a dependency
    // on the id name that can be avoided when just appending a word
    let saveBtnId = editIcon.id + '-save';
    // Add save button if not already existing but hidden until an input is made
    if (document.querySelector('#' + saveBtnId) === null) {
        fieldContainer.insertAdjacentHTML('afterbegin', `<img src="assets/general/general-img/checkmark.svg"
                                                      class="contenteditable-save-icon cursor-pointer" alt="Save"
                                                      id="${saveBtnId}" style="display: none">`);
    }
    let saveBtn = document.getElementById(saveBtnId);

    // Save on enter key press
    fieldContainer.addEventListener('keypress', function (e) {
        // Save on enter keypress or ctrl enter / cmd enter
        if (e.key === 'Enter' || (e.ctrlKey || e.metaKey) && (e.keyCode === 13 || e.keyCode === 10)) {
            // Prevent new line on enter key press
            e.preventDefault();
            // Triggers focusout event that is caught in event listener and saves client value
            field.contentEditable = 'false';
        }
    });
    // Display save button after the first input
    fieldContainer.addEventListener('input', () => {
        if (saveBtn.style.display === 'none') {
            saveBtn.style.display = 'inline-block';
        }
    });
}

export function disableEditableField(field) {
    let fieldContainer = field.parentNode;

    // If empty submit value successfully submitted, and it doesn't have data-hide-if-empty="true",
    // add a &nbsp; for it to be visible on hover and edited later
    if (field.textContent.trim() === '' && fieldContainer.dataset.hideIfEmpty !== 'true') {
        fieldContainer.querySelector(fieldContainer.dataset.fieldElement).innerHTML = '&nbsp;';
    }
    field.contentEditable = 'false';
    fieldContainer.querySelector('.contenteditable-edit-icon').style.display = null; // Default display
    // I don't know why but the focusout event is triggered multiple times when clicking on the edit icon again
    let saveIcon = fieldContainer.querySelector('.contenteditable-save-icon');

    // Only remove it if it exists to prevent error in case field was unchanged and save icon not displayed
    saveIcon?.remove();
}

/**
 * Frontend validation of contenteditable field
 * and request to update value if valid.
 *
 * @return boolean
 */
export function contentEditableFieldValueIsValid(field) {
    // "this" is the field
    let textContent = field.textContent.trim();
    let fieldName = field.dataset.name;

    let required = field.dataset.required;
    if (required !== undefined && required === 'true' && textContent.length === 0) {
        displayValidationErrorMessage(fieldName, 'Required field');
        return false;
    }

    // Check that length is either 0 or more than given minlength (0 is checked with required above)
    let minLength = field.dataset.minlength;
    if (minLength !== undefined && (textContent.length < parseInt(minLength) && textContent.length !== 0)) {
        displayValidationErrorMessage(fieldName, 'Minimum length is ' + minLength);
        return false;
    }

    // Check that length is either 0 or more than given maxlength
    let maxLength = field.dataset.maxlength;
    if (maxLength !== undefined && (textContent.length > parseInt(maxLength) && textContent.length !== 0)) {
        displayValidationErrorMessage(fieldName, 'Maximum length is ' + maxLength);
        return false;
    }

    // If no validation error was found
    return true;
}