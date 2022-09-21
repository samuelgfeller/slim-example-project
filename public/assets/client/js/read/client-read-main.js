import {
    initActivityTextareasEventListeners, initAllDeleteBtnEventListeners
} from "./client-read-text-area-event-listener-setup.js";
import {addNewNoteTextarea} from "./client-read-create-note.js";
import {saveClientReadDropdownChange} from "./client-read-save-dropdown-change.js";
import {loadClientNotes} from "./client-read-note-loading.js";

loadClientNotes(() => {
    // Script loaded with defer so waiting for DOMContentLoaded is not needed
    initActivityTextareasEventListeners();
    // Add note delete btn event listeners
    // The reason it is not in initTextareasEventListeners() is that event listener were set up twice and alert modal
    // were displayed one on top of the other and thus not working. Turns out the reason was that I called initAllDeleteBtnEventListeners
    // AND initActivityTextareasEventListeners that already contained initAllDeleteBtnEventListeners
    initAllDeleteBtnEventListeners();

    // Manually init autoResizingTextareas to include the new ones as it's only done during page load and not afterwards
    initAutoResizingTextareas();
});


// After plus button is clicked, textarea for new note should be added
document.querySelector('#create-note-btn').addEventListener('click', addNewNoteTextarea);

const clientStatus = document.querySelector('select[name="client_status_id"]');
clientStatus.addEventListener('change', saveClientReadDropdownChange);

const assignedUser = document.querySelector('select[name="user_id"]');
assignedUser.addEventListener('change', saveClientReadDropdownChange);
