import {makeUserFieldEditable} from "./user-update-contenteditable.js?v=0.2.0";
import {displayChangePasswordModal} from "../update/change-password-modal.html.js?v=0.2.0";
import {displayFlashMessage} from "../../general/page-component/flash-message/flash-message.js?v=0.2.0";
import {submitModalForm} from "../../general/page-component/modal/modal-submit-request.js?v=0.2.0";
import {submitFieldChangeWithFlash} from "../../general/ajax/submit-field-change-with-flash.js?v=0.2.0";
import {submitDelete} from "../../general/ajax/submit-delete-request.js?v=0.2.0";
import {createAlertModal} from "../../general/page-component/modal/alert-modal.js?v=0.2.0";
import {loadUserActivities} from "./user-activity/activity-main.js?v=0.2.0";

const userId = document.getElementById('user-id').value;

loadUserActivities(`user=${userId}`);

// Null safe operator as edit icon doesn't exist if not privileged
document.querySelector('#edit-first-name-btn')?.addEventListener('click', makeUserFieldEditable);
document.querySelector('#edit-last-name-btn')?.addEventListener('click', makeUserFieldEditable);
document.querySelector('#edit-email-btn')?.addEventListener('click', makeUserFieldEditable);

// User status dropdown change
document.querySelector('select[name="status"]:not([disabled])')
    ?.addEventListener('change', submitUserDropdownChange);
// User role dropdown change
document.querySelector('select[name="user_role_id"]:not([disabled])')
    ?.addEventListener('change', submitUserDropdownChange);

// Delete button with null safe as it doesn't exist when not privileged
const userBtn = document.querySelector('#delete-user-btn');
userBtn?.addEventListener('click', () => {
    let title = 'Are you sure that you want to delete this user?';
    let info = '';
    if(userBtn.dataset.isOwnProfile === '1'){
        title = 'Are you sure that you want to delete your profile?';
        info = 'You will be logged out and not be able to log in again.'
    }
    createAlertModal(title, info, () => {
        submitDelete(`users/${userId}`, true).then(() => {
            if(userBtn.dataset.isOwnProfile === '1'){
                location.href = `login`;
            }else {
                location.href = `users/list`;
            }
        });
    });
});

/**
 * User select change event handler
 */
function submitUserDropdownChange() {
    // "this" is the select element
    // Submit field change with flash message indicating that change was successful
    submitFieldChangeWithFlash(this.name, this.value, `users/${userId}`, true, false);
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