import {displayClientCreateModal} from "./client-create-modal.html.js?v=1.0.0";
import {displayFlashMessage} from "../../general/page-component/flash-message/flash-message.js?v=1.0.0";
import {displayValidationErrorMessage} from "../../general/validation/form-validation.js?v=1.0.0";
import {fetchAndLoadClients} from "../list/client-list-loading.js?v=1.0.0";
import {__} from "../../general/general-js/functions.js?v=1.0.0";
import {fetchTranslations} from "../../general/ajax/fetch-translation-data.js?v=1.0.0";
import {submitModalForm} from "../../general/ajax/modal-submit-request.js?v=1.0.0";

// Init event listeners if button is present
document.getElementById('create-client-btn')?.addEventListener('click', displayClientCreateModal);

// Fetch needed translations for creating a client
let wordsToTranslate = [
    __('Please fill out either the first name or last name'),
    __('Client created successfully.'),
];
// Init translated var by populating it with english values as a default so that all keys are existing
let translated = Object.fromEntries(wordsToTranslate.map(value => [value, value]));
// Fetch translations and replace str var (fetch done automatically at page loading when imported)
fetchTranslations(wordsToTranslate).then(response => {
    // Fill the var with a JSON of the translated words. Key is the original english words and value the translated one
    translated = response;
});
// Submit form on create button click
document.addEventListener('click', e => {
    // Event delegation as modal is removed and added dynamically
    if (e.target && e.target.id === 'client-create-submit-btn') {
        // Additional frontend validation: check that either firstname or last name is set
        let form = document.getElementById('create-client-modal-form');
        if (form.querySelector('#first-name-input').value === '' &&
            form.querySelector('#last-name-input').value === ''
        ) {
            displayValidationErrorMessage(
                'first_name',
                translated['Please fill out either the first name or last name']
            );
            return;
        }
        // Submit modal form and execute promise "then()" only if available (nothing is returned on validation error)
        submitModalForm('create-client-modal-form', 'clients', 'POST')
            ?.then(responseJson => {
                displayFlashMessage('success', translated['Client created successfully.']);
                fetchAndLoadClients();
            }).catch(error => {
            console.error(error);
        });
    }
});
