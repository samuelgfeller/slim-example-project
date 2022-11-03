import {submitClientUpdate} from "./client-update-request.js";

/**
 * Make text value as editable and attach event listeners
 */
export function makeClientValueEditable() {
    let editIcon = this;
    let h1Container = this.parentNode;
    let h1 = h1Container.querySelector('h1');

    editIcon.style.display = 'none';

    // Slick would be to replace the word "edit" of the edit icon for the save button but that puts a dependency
    // on the id that can be avoided when just appending a word
    let saveBtnId = editIcon.id + '-save';

    h1.contentEditable = 'true';
    // Add save button but hidden until an input is made
    h1Container.insertAdjacentHTML('afterbegin', `<img src="assets/general/img/checkmark.svg"
                                                      class="contenteditable-save-icon cursor-pointer" alt="Save"
                                                      id="${saveBtnId}" style="display: none">`);
    let saveBtn = document.getElementById(saveBtnId);

    h1Container.addEventListener('keypress', function (e) {
        // Save on enter keypress or ctrl enter / cmd enter
        if (e.key === 'Enter' || (e.ctrlKey || e.metaKey) && (e.keyCode === 13 || e.keyCode === 10)) {
            // Prevent new line
            e.preventDefault();
            saveClientValue(h1, editIcon, saveBtn);
        }
    });
    h1Container.addEventListener('input', () => {
        if (saveBtn.style.display === 'none') {
            saveBtn.style.display = 'inline-block';
        }
    });
    saveBtn.addEventListener('click', e => {
        saveClientValue(h1, editIcon, saveBtn);
    })
}

function saveClientValue(h1, editIcon, saveBtn) {
    h1.contentEditable = 'false';
    editIcon.style.display = null; // Default display
    saveBtn.remove();
    console.log(h1.dataset.name);
    submitClientUpdate(h1.dataset.name, h1.textContent.trim());
}