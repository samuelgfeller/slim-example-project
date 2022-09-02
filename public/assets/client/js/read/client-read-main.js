import {initActivityTextareasEventListeners} from "./client-read-text-area-event-listener-setup.js";
import {addNewNoteTextarea} from "./client-read-create-note.js";


// Script loaded with defer so waiting for DOMContentLoaded is not needed
initActivityTextareasEventListeners();

// After plus button is clicked, textarea for new note should be added
document.querySelector('#create-note-btn').addEventListener('click', addNewNoteTextarea);

const clientStatus = document.querySelector('select[name="client_status"]');
clientStatus.addEventListener('change', function (e) {
    // Put selected option into select data attribute
    this.dataset.color = this.value;
    switch (this.innerText) {
        // case ''
    }
});







