import {displayUserCreateModal} from "./user-create-modal.html.js?v=0.4.0";
import {submitModalForm} from "../../general/page-component/modal/modal-submit-request.js?v=0.4.0";
import {displayFlashMessage} from "../../general/page-component/flash-message/flash-message.js?v=0.4.0";
import {loadUserList} from "../list/user-list-loading.js?v=0.4.0";
import {fetchTranslations} from "../../general/ajax/fetch-translation-data.js?v=0.4.0";
import {__} from "../../general/general-js/functions.js?v=0.4.0";

// List of words that are used in modal box and need to be translated
let wordsToTranslate = [
    __('User created successfully'),
];
// Init translated var by populating it with english values as a default so that all keys are existing
let translated = Object.fromEntries(wordsToTranslate.map(value => [value, value]));
// Fetch translations and replace translated var
fetchTranslations(wordsToTranslate).then(response => {
    // Fill the var with a JSON of the translated words. Key is the original english words and value the translated one
    translated = response;
});

document.querySelector('#create-user-btn').addEventListener('click', displayUserCreateModal);
// Modal events need event delegation as modal is removed and added dynamically
document.addEventListener('click', e => {
// Submit request on submit button click
    if (e.target && e.target.id === 'user-create-submit-btn') {

        // Submit modal form and execute promise "then()" only if available (nothing is returned on validation error)
        submitModalForm('create-user-modal-form', 'users', 'POST')?.then((response) => {
            if (response.status === 'error') {
                displayFlashMessage('error', response.message);
            } else {
                displayFlashMessage('success', translated['User created successfully']);
            }
            loadUserList();
        })
    }
});