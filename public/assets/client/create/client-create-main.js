import {displayClientCreateModal} from "./client-create-modal.html.js?v=0.1";
import {submitModalForm} from "../../general/js/modal/modal-submit-request.js?v=0.1";
import {displayFlashMessage} from "../../general/js/requestUtil/flash-message.js?v=0.1";
import {displayValidationErrorMessage} from "../../general/js/validation/form-validation.js?v=0.1";
import {fetchAndLoadClients} from "../list/client-list-loading.js?v=0.1";

// Init event listeners
document.getElementById('create-client-btn').addEventListener('click', displayClientCreateModal);

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
                'Please fill out either the first name or last name'
            );
            return;
        }
        // Submit modal form and execute promise "then()" only if available (nothing is returned on validation error)
        submitModalForm('create-client-modal-form', 'clients', 'POST')?.then(() => {
            displayFlashMessage('success', 'Client created successfully.');
            fetchAndLoadClients();
        })
    }
});