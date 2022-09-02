import {initActivityTextareasEventListeners} from "./client-read-text-area-event-listener-setup.js";
import {addNewNoteTextarea} from "./client-read-create-note.js";
import {saveClientReadDropdownChange} from "./client-read-save-dropdown-change.js";


// Script loaded with defer so waiting for DOMContentLoaded is not needed
initActivityTextareasEventListeners();

// After plus button is clicked, textarea for new note should be added
// document.querySelector('#create-note-btn').addEventListener('click', addNew$NoteTextarea);

const clientStatus = document.querySelector('select[name="client_status_id"]');
clientStatus.addEventListener('change', saveClientReadDropdownChange);

const assignedUser = document.querySelector('select[name="user_id"]');
assignedUser.addEventListener('change', saveClientReadDropdownChange);
