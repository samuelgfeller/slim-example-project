import {initNotesEventListeners} from "./client-read-note-event-listener-setup.js?v=0.1";
import {addNewNoteTextarea} from "./client-read-create-note.js?v=0.1";
import {loadClientNotes} from "./client-read-note-loading.js?v=0.1";
import {makeClientFieldEditable} from "./update/client-update-contenteditable.js?v=0.1";
import {makeFieldSelectValueEditable} from "./update/client-update-dropdown.js?v=0.1";
import {loadAvailablePersonalInfoIconsDiv} from "./client-read-personal-info.js?v=0.1";
import {submitFieldChangeWithFlash} from "../../general/js/request/submit-field-change-with-flash.js?v=0.1";
import {initAutoResizingTextareas} from "../../general/js/pageComponents/auto-resizing-textarea.js?v=0.1";
import {scrollToAnchor} from "../../general/js/page/scroll-to-anchor.js?v=0.1";
import {createAlertModal} from "../../general/js/modal/alert-modal.js?v=0.1";
import {submitDelete} from "../../general/js/request/submit-delete-request.js?v=0.1";

const clientId = document.getElementById('client-id').value;

loadClientNotes(() => {
    // Script loaded with defer so waiting for DOMContentLoaded is not needed
    initNotesEventListeners();
    // Add note delete btn event listeners
    // The reason it is not in initNotesEventListeners() is that event listener were set up twice and alert modal
    // were displayed one on top of the other and thus not working. Turns out the reason was that I called initAllButtonsAboveNotesEventListeners
    // AND initActivityTextareasEventListeners that already contained initAllDeleteBtnEventListeners
    // initAllButtonsAboveNotesEventListeners();

    // Manually init autoResizingTextareas to include the loaded notes as it's only done during page load and not afterwards
    initAutoResizingTextareas();
    scrollToAnchor();
});

loadAvailablePersonalInfoIconsDiv();

// New note button event listener
// After plus button is clicked, textarea for new note should be added
document.querySelector('#create-note-btn').addEventListener('click', addNewNoteTextarea);

// Dropdown client status and assigned user change event listener
document.querySelector('select[name="client_status_id"]:not([disabled])')
    ?.addEventListener('change', submitClientDropdownChange);
document.querySelector('select[name="user_id"]:not([disabled])')
    ?.addEventListener('change', submitClientDropdownChange);

// Edit client main values event listeners
// First and last name
document.querySelector('#edit-first-name-btn')?.addEventListener('click', makeClientFieldEditable);
document.querySelector('#edit-last-name-btn')?.addEventListener('click', makeClientFieldEditable);
// Personal info
document.querySelector('#edit-location-btn')?.addEventListener('click', makeClientFieldEditable);
document.querySelector('#edit-phone-btn')?.addEventListener('click', makeClientFieldEditable);
document.querySelector('#edit-email-btn')?.addEventListener('click', makeClientFieldEditable);
document.querySelector('#edit-birthdate-btn')?.addEventListener('click', makeClientFieldEditable);
document.querySelector('#edit-sex-btn')?.addEventListener('click', makeFieldSelectValueEditable);
// Delete button
document.querySelector('#delete-client-btn')?.addEventListener('click', () => {
    let title = 'Are you sure that you want to delete this client?';
    createAlertModal(title, '', () => {
        submitDelete(`clients/${clientId}`, true).then(() => {
            location.href = `clients/list`;
        });
    });
});

// Toggle personal info edit icons
let personalInfoEditIconsToggle = document.querySelector('#toggle-personal-info-edit-icons');
let personalInfoContainer = document.querySelector('#client-personal-info-flex-container');
personalInfoEditIconsToggle.addEventListener('click', () => {
    let personalInfosEditIcons = document.querySelectorAll('#client-personal-info-flex-container div .contenteditable-edit-icon');
    for (let editIcon of personalInfosEditIcons) {
        editIcon.classList.toggle('always-displayed-icon');
    }
})

// Display toggle if screen is touch device https://stackoverflow.com/a/13470899/9013718
if ('ontouchstart' in window || navigator.msMaxTouchPoints) {
    personalInfoEditIconsToggle.style.display = 'inline-block';
    // Increase right padding to not overlap edit icons
    personalInfoContainer.style.paddingRight = '20px';
} else {
    personalInfoEditIconsToggle.style.display = 'none';
    personalInfoContainer.style.paddingRight = null;
}

/**
 * Client select change event handler
 */
function submitClientDropdownChange() {
    // "this" is the select element
    // Submit field change with flash message indicating that change was successful
    submitFieldChangeWithFlash(this.name, this.value, `clients/${clientId}`, `clients/${clientId}`);
}