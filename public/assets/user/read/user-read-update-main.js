import {makeUserFieldEditable} from "./user-update-contenteditable.js";
import {displayChangePasswordModal} from "../update/change-password-modal.html.js";
import {displayFlashMessage} from "../../general/js/requests/flash-message.js";
import {submitModalForm} from "../../general/js/modal/modal-submit-request.js";
import {submitUserUpdate} from "../update/user-update-request.js";

document.querySelector('#edit-first-name-btn')?.addEventListener('click', makeUserFieldEditable);
document.querySelector('#edit-last-name-btn')?.addEventListener('click', makeUserFieldEditable);
document.querySelector('#edit-email-btn')?.addEventListener('click', makeUserFieldEditable);

// User dropdown change event listeners
const statusSelect = document.querySelector('select[name="status"]:not([disabled])');
statusSelect?.addEventListener('change', () => {
    submitUserUpdate({[statusSelect.name]: statusSelect.value}).then(success =>
        success === true ? displayFlashMessage('success', 'Successfully changed status.') : null
    );
});
const userRoleSelect = document.querySelector('select[name="user_role_id"]:not([disabled])');
userRoleSelect?.addEventListener('change', () => {
    submitUserUpdate({[userRoleSelect.name]: userRoleSelect.value}).then( success =>
        success === true ? displayFlashMessage('success', 'Successfully changed role.') : null
    );
});

// Display all edit icons if touch screen
if ('ontouchstart' in window || navigator.msMaxTouchPoints) {
    let editIcons = document.querySelectorAll('.contenteditable-edit-icon');
    for (let editIcon of editIcons) {
        editIcon.classList.toggle('always-displayed-icon');
    }
}

// Change password modal
document.getElementById('change-password-btn')?.addEventListener('click', displayChangePasswordModal);

// Delegated event listener as element doesn't exist on page load
// Submit form on submit button click
document.addEventListener('click', e => {
    if (e.target && e.target.id === 'change-password-submit-btn') {
        let userId = document.getElementById('user-id').value;
        submitModalForm('change-password-modal-form', `change-password/${userId}`, 'PUT', `users/${userId}`)
            .then(() => {
                displayFlashMessage('success', 'Successfully changed password.');
            });
    }
});