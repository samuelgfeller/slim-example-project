import {makeClientFieldEditable} from "./update/client-update-contenteditable.js?v=0.4.0";
import {makeFieldSelectValueEditable} from "./update/client-update-dropdown.js?v=0.4.0";
import {loadAvailablePersonalInfoIconsDiv} from "./client-read-personal-info.js?v=0.4.0";
import {createAlertModal} from "../../general/page-component/modal/alert-modal.js?v=0.4.0";
import {submitDelete} from "../../general/ajax/submit-delete-request.js?v=0.4.0";
import {submitUpdate} from "../../general/ajax/submit-update-data.js?v=0.4.0";
import {fetchAndLoadClientNotes} from "../note/client-read-note-loading.js?v=0.4.0";
import {addNewNoteTextarea} from "../note/client-read-create-note.js?v=0.4.0";
import {fetchTranslations} from "../../general/ajax/fetch-translation-data.js?v=0.4.0";
import {__} from "../../general/general-js/functions.js?v=0.4.0";

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
document.querySelector('h1[data-name="first_name"]')?.addEventListener('dblclick', makeClientFieldEditable);
document.querySelector('#edit-first-name-btn')?.addEventListener('click', makeClientFieldEditable);
document.querySelector('#edit-last-name-btn')?.addEventListener('click', makeClientFieldEditable);
document.querySelector('h1[data-name="last_name"]')?.addEventListener('dblclick', makeClientFieldEditable);
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

// Retrieve needed translations for deletion
let wordsToTranslate = [
    __('Are you sure that you want to delete this client?'),
    __('Are you sure that you want to restore this client?'),
    __('Yes undelete'),
];
// Init translated var by populating it with english values as a default so that all keys are existing
let translated = Object.fromEntries(wordsToTranslate.map(value => [value, value]));
// Fetch translations and replace translated var
fetchTranslations(wordsToTranslate).then(response => {
    // Fill the var with a JSON of the translated words. Key is the original english words and value the translated one
    translated = response;
});
// Delete button
document.querySelector('#delete-client-btn')?.addEventListener('click', () => {
    let title = translated['Are you sure that you want to delete this client?'];
    createAlertModal(title, '', () => {
        submitDelete(`clients/${clientId}`, true).then(() => {
            location.href = `clients/list`;
        });
    });
});
// Restore / undelete button
document.querySelector('#undelete-client-btn')?.addEventListener('click', () => {
    let title = translated['Are you sure that you want to restore this client?'];
    createAlertModal(title, '', () => {
        submitUpdate({'deleted_at': null}, `clients/${clientId}`,
            `clients/${clientId}`).then(() => {
            location.reload();
        });
    }, translated['Yes undelete']);
});


// Toggle personal info edit icons
let personalInfoContainer = document.querySelector('#client-personal-info-flex-container');
let personalInfoEditIconsToggle = document.querySelector('#toggle-personal-info-edit-icons');
// PersonalInfoEditIconsToggle is not present if the user doesn't have update permission
if (personalInfoEditIconsToggle) {
    personalInfoEditIconsToggle.addEventListener('click', () => {
        let personalInfosEditIcons = document.querySelectorAll('#client-personal-info-flex-container div .contenteditable-edit-icon');
        for (let editIcon of personalInfosEditIcons) {
            editIcon.classList.toggle('always-displayed-icon');
        }
    })

// Display toggle btn if screen is touch device https://stackoverflow.com/a/13470899/9013718
    if ('ontouchstart' in window || navigator.msMaxTouchPoints) {
        personalInfoEditIconsToggle.style.display = 'inline-block';
        // Increase right padding to not overlap edit icons
        personalInfoContainer.style.paddingRight = '20px';
    } else {
        personalInfoEditIconsToggle.style.display = 'none';
        personalInfoContainer.style.paddingRight = null;
    }
}

/**
 * Client select change event handler
 */
function submitClientDropdownChange() {
    // "this" is the select element
    // Submit field change with flash message indicating that change was successful
    submitUpdate({[this.name]: this.value}, `clients/${clientId}`, true)
        .then(r => {
        });
}

function changeMainNoteBorderAccordingToVigilanceLevel(vigilanceLevel) {
    let mainNote = document.querySelector('#main-note-textarea-div textarea');
    mainNote.classList.remove('vigilance-low', 'vigilance-medium', 'vigilance-high');
    switch (vigilanceLevel) {
        case 'low':
            mainNote.classList.add('vigilance-low');
            break;
        case 'medium':
            mainNote.classList.add('vigilance-medium');
            break;
        case 'high':
            mainNote.classList.add('vigilance-high');
            break;
        default:
            break;
    }
}
