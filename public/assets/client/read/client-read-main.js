import {makeClientFieldEditable} from "./update/client-update-contenteditable.js?v=0.2.0";
import {makeFieldSelectValueEditable} from "./update/client-update-dropdown.js?v=0.2.0";
import {loadAvailablePersonalInfoIconsDiv} from "./client-read-personal-info.js?v=0.2.0";
import {submitFieldChangeWithFlash} from "../../general/ajax/submit-field-change-with-flash.js?v=0.2.0";
import {createAlertModal} from "../../general/page-component/modal/alert-modal.js?v=0.2.0";
import {submitDelete} from "../../general/ajax/submit-delete-request.js?v=0.2.0";
import {submitUpdate} from "../../general/ajax/submit-update-data.js?v=0.2.0";
import {fetchAndLoadClientNotes} from "../note/client-read-note-loading.js?v=0.2.0";
import {addNewNoteTextarea} from "../note/client-read-create-note.js?v=0.2.0";

const clientId = document.getElementById('client-id').value;

fetchAndLoadClientNotes();

loadAvailablePersonalInfoIconsDiv();

// Change main note border color if vigilance level is set
let vigilanceLevel = document.getElementById('vigilance-level-select')?.value;
changeMainNoteBorderAccordingToVigilanceLevel(vigilanceLevel);

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
const vigilanceLevelEditBtn = document.querySelector('#edit-vigilance-level-btn');
vigilanceLevelEditBtn?.addEventListener('click', () => {
    makeFieldSelectValueEditable.call(vigilanceLevelEditBtn).then(changeMainNoteBorderAccordingToVigilanceLevel);
});
// Delete button
document.querySelector('#delete-client-btn')?.addEventListener('click', () => {
    let title = 'Are you sure that you want to delete this client?';
    createAlertModal(title, '', () => {
        submitDelete(`clients/${clientId}`, true).then(() => {
            location.href = `clients/list`;
        });
    });
});
// Restore / undelete button
document.querySelector('#undelete-client-btn')?.addEventListener('click', () => {
    let title = 'Are you sure that you want to restore this client?';
    createAlertModal(title, '', () => {
        submitUpdate({'deleted_at': null}, `clients/${clientId}`,
            `clients/${clientId}`).then(() => {
            location.reload();
        });
    }, 'Yes undelete');
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
    submitFieldChangeWithFlash(this.name, this.value, `clients/${clientId}`, true, false);
}

function changeMainNoteBorderAccordingToVigilanceLevel(vigilanceLevel) {
    let mainNote = document.querySelector('#main-note-textarea-div textarea');
    switch (vigilanceLevel) {
        case 'moderate':
            mainNote.style.boxShadow = '0 0 10px 5px rgb(227, 193, 28)';
            // mainNote.style.borderColor = 'rgb(227, 193, 28)';
            break;
        case 'caution':
            mainNote.style.boxShadow = '0 0 10px 5px rgb(232, 136, 26)';
            // mainNote.style.borderColor = 'rgb(232, 136, 26)';
            break;
        case 'extra_caution':
            // mainNote.style.borderColor = 'rgb(224, 77, 29)';
            mainNote.style.boxShadow = '0 0 10px 5px rgb(224, 77, 29)';
            break;
        default:
            // mainNote.style.borderColor = '#2e3e50';
            mainNote.style.boxShadow = 'none';
            break;
    }
}
