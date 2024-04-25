import {displayValidationErrorMessage} from "../../validation/form-validation.js?v=0.4.0";
import {fetchTranslations} from "../../ajax/fetch-translation-data.js?v=0.4.0";
import {__} from "../../general-js/functions.js?v=0.4.0";

/**
 * Make field value editable, add save button and focus it.
 * Documentation: https://github.com/samuelgfeller/slim-example-project/wiki/JavaScript-Frontend#contenteditable-fields
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

    // Disable drop of the field otherwise it'd be possible to drag the field and drop the html it in the same field
    field.addEventListener('drop', (e) => {
        e.preventDefault();
    });

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

    // Save on enter key press (js does not add identical event listeners when the same handler function is used)
    fieldContainer.addEventListener('keypress', saveFieldOnEnterKeyPressEventHandler);

    // Display save button after the first input
    fieldContainer.addEventListener('input', () => {
        if (saveBtn.style.display === 'none') {
            saveBtn.style.display = 'inline-block';
        }
    });
}

/**
 * Save field on enter key press or ctrl enter / cmd enter.
 * Must be in separate function so that event listener is added only once.
 * @param e event
 */
function saveFieldOnEnterKeyPressEventHandler(e) {
    // Save on enter keypress or ctrl enter / cmd enter
    if (e.key === 'Enter' || (e.ctrlKey || e.metaKey) && (e.keyCode === 13 || e.keyCode === 10)) {
        // Prevent new line on enter key press
        e.preventDefault();

        // Create a focusable dummy input element
        let dummyInput = document.createElement('input');
        document.body.appendChild(dummyInput);

        // Triggers focusout event that is caught in event listener and saves value
        dummyInput.focus();

        // Remove the dummy input element
        document.body.removeChild(dummyInput);
    }
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

// List of words that are used in contenteditable validation that need to be translated
let wordsToTranslate = [
    __('Required'),
    __('Minimum length is'),
    __('Maximum length is'),
];
// Init translated var by populating it with english values as a default so that all keys are existing
let translated = Object.fromEntries(wordsToTranslate.map(value => [value, value]));
// Fetch translations and replace translated var
fetchTranslations(wordsToTranslate).then(response => {
    // Fill the var with a JSON of the translated words. Key is the original english words and value the translated one
    translated = response;
});

/**
 * Frontend validation of contenteditable field
 * and request to update value if valid.
 *
 * @return boolean
 */
export function contentEditableFieldValueIsValid(field) {
    let textContent = field.textContent.trim();
    let fieldName = field.dataset.name;

    let required = field.dataset.required;
    if (required !== undefined && required === 'true' && textContent.length === 0) {
        displayValidationErrorMessage(fieldName, translated['Required']);
        return false;
    }

    // Check that length is either 0 or more than given minlength (0 is checked with required above)
    let minLength = field.dataset.minlength;
    if (minLength !== undefined && (textContent.length < parseInt(minLength) && textContent.length !== 0)) {
        displayValidationErrorMessage(fieldName, translated['Minimum length is'] + ' ' + minLength);
        return false;
    }

    // Check that length is either 0 or more than given maxlength
    let maxLength = field.dataset.maxlength;
    if (maxLength !== undefined && (textContent.length > parseInt(maxLength) && textContent.length !== 0)) {
        displayValidationErrorMessage(fieldName, translated['Maximum length is'] + ' ' + maxLength);
        return false;
    }

    // If no validation error was found
    return true;
}