import {makeUserFieldEditable} from "./user-update-contenteditable.js?v=0.1";
import {displayChangePasswordModal} from "../update/change-password-modal.html.js?v=0.1";
import {displayFlashMessage} from "../../general/js/requestUtil/flash-message.js?v=0.1";
import {submitModalForm} from "../../general/js/modal/modal-submit-request.js?v=0.1";
import {submitFieldChangeWithFlash} from "../../general/js/request/submit-field-change-with-flash.js?v=0.1";

document.querySelector('#edit-first-name-btn')?.addEventListener('click', makeUserFieldEditable);
document.querySelector('#edit-last-name-btn')?.addEventListener('click', makeUserFieldEditable);
document.querySelector('#edit-email-btn')?.addEventListener('click', makeUserFieldEditable);

// User status dropdown change
document.querySelector('select[name="status"]:not([disabled])')
    ?.addEventListener('change', submitUserDropdownChange);
// User role dropdown change
document.querySelector('select[name="user_role_id"]:not([disabled])')
    ?.addEventListener('change', submitUserDropdownChange);


/**
 * User select change event handler
 */
function submitUserDropdownChange() {
    // "this" is the select element
    let userId = document.getElementById('user-id').value;
    // Submit field change with flash message indicating that change was successful
    submitFieldChangeWithFlash(this.name, this.value, `users/${userId}`, `users/${userId}`);
}

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