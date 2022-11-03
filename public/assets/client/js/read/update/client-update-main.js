import {submitClientUpdate} from "./client-update-request.js";

function preventLinkOpening(e) {
    /* Prevent link from being opened */
    e.preventDefault();
}

/**
 * Make text value as editable and attach event listeners
 */
export function makeClientValueEditable() {

    let editIcon = this;
    let fieldContainer = this.parentNode;
    let fieldElement = fieldContainer.dataset.fieldElement;
    let field = fieldContainer.querySelector(fieldElement);
    // Field element is usually the field element but there are special cases like when the parent is <a> and content span
    if (fieldElement === 'a-span') {
        field = fieldContainer.querySelector('span');
        let a = fieldContainer.closest('a');
        // Add class to prevent :focus css rule. It is removed in saveClientValue()
        a.classList.add('currently-editable');
        // Add
        a.addEventListener('click', preventLinkOpening);
    }

    editIcon.style.display = 'none';

    // Slick would be to replace the word "edit" of the edit icon with "save" for the save button but that puts a dependency
    // on the id name that can be avoided when just appending a word
    let saveBtnId = editIcon.id + '-save';

    field.contentEditable = 'true';
    field.focus();

    // Add save button but hidden until an input is made
    fieldContainer.insertAdjacentHTML('afterbegin', `<img src="assets/general/img/checkmark.svg"
                                                      class="contenteditable-save-icon cursor-pointer" alt="Save"
                                                      id="${saveBtnId}" style="display: none">`);
    let saveBtn = document.getElementById(saveBtnId);

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
    saveBtn.addEventListener('click', () => {
        // Triggers focusout event that is caught in event listener and saves client value
        field.contentEditable = 'false';
    });
    // Save on focusout - has to be direct function call and not anonymous function as otherwise the
    // event listener would be registered multiple times: https://stackoverflow.com/a/47337711/9013718
    field.addEventListener('focusout', saveClientValue);
}

/**
 * Make field non-editable and make call function that
 * makes client update request
 */
function saveClientValue() {
    // "this" is the field
    let fieldContainer = this.parentNode;
    // Remove event listener that prevented the link (parent of span) from opening
    if (fieldContainer.dataset.fieldElement === 'a-span') {
        let a = fieldContainer.closest('a');
        a.classList.remove('currently-editable');
        a.removeEventListener('click', preventLinkOpening);
    }
    this.contentEditable = 'false';
    fieldContainer.querySelector('.contenteditable-edit-icon').style.display = null; // Default display
    // I don't know why but the focusout event is triggered multiple times when clicking on the edit icon again
    let saveIcon = fieldContainer.querySelector('.contenteditable-save-icon');
    // Only remove it if it exists to prevent error
    saveIcon.remove();
    submitClientUpdate(this.dataset.name, this.textContent.trim());

}