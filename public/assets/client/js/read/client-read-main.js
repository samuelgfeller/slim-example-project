import {
    initNotesEventListeners, initAllDeleteBtnEventListeners
} from "./client-read-text-area-event-listener-setup.js";
import {addNewNoteTextarea} from "./client-read-create-note.js";
import {saveClientReadDropdownChange} from "./client-read-save-dropdown-change.js";
import {loadClientNotes} from "./client-read-note-loading.js";
import {initAutoResizingTextareas} from "../../../general/js/default.js";
import {makeFieldValueEditable} from "./update/client-update-contenteditable.js";
import {makeFieldSelectValueEditable} from "./update/client-update-dropdown.js";

loadClientNotes(() => {
    // Script loaded with defer so waiting for DOMContentLoaded is not needed
    initNotesEventListeners();
    // Add note delete btn event listeners
    // The reason it is not in initNotesEventListeners() is that event listener were set up twice and alert modal
    // were displayed one on top of the other and thus not working. Turns out the reason was that I called initAllDeleteBtnEventListeners
    // AND initActivityTextareasEventListeners that already contained initAllDeleteBtnEventListeners
    initAllDeleteBtnEventListeners();

    // Manually init autoResizingTextareas to include the new ones as it's only done during page load and not afterwards
    initAutoResizingTextareas();
});


// New note button event listener
// After plus button is clicked, textarea for new note should be added
document.querySelector('#create-note-btn').addEventListener('click', addNewNoteTextarea);

// Dropdown client status and assigned user change event listener
const clientStatus = document.querySelector('select[name="client_status_id"]:not([disabled])');
clientStatus?.addEventListener('change', saveClientReadDropdownChange);
const assignedUser = document.querySelector('select[name="user_id"]:not([disabled])');
assignedUser?.addEventListener('change', saveClientReadDropdownChange);

// Edit client main values event listeners
// First and last name
document.querySelector('#edit-first-name-btn')?.addEventListener('click', makeFieldValueEditable);
document.querySelector('#edit-last-name-btn')?.addEventListener('click', makeFieldValueEditable);
// Personal info
document.querySelector('#edit-location-btn')?.addEventListener('click', makeFieldValueEditable);
document.querySelector('#edit-phone-btn')?.addEventListener('click', makeFieldValueEditable);
document.querySelector('#edit-email-btn')?.addEventListener('click', makeFieldValueEditable);
document.querySelector('#edit-birthdate-btn')?.addEventListener('click', makeFieldValueEditable);
document.querySelector('#edit-sex-btn')?.addEventListener('click', makeFieldSelectValueEditable);
// Add new personal info
let newPersonalInfoIconDiv = document.querySelector('#add-client-personal-info-btn-div');
