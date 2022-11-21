import {displayUserCreateModal} from "./user-create-modal.html.js?v=0.1";
import {submitModalForm} from "../../general/js/modal/modal-submit-request.js?v=0.1";
import {displayFlashMessage} from "../../general/js/requestUtil/flash-message.js?v=0.1";

document.querySelector('#create-user-btn').addEventListener('click', displayUserCreateModal);

// Modal events need event delegation as modal is removed and added dynamically
document.addEventListener('click', e => {
// Submit request on submit button click
    if (e.target && e.target.id === 'user-create-submit-btn') {

        // Submit modal form and execute promise "then()" only if available (nothing is returned on validation error)
        submitModalForm('create-user-modal-form', 'users', 'POST')?.then(() => {
            displayFlashMessage('success', 'User created successfully.');
            // loadUsers();
        })
    }
});