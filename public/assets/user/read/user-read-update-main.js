import {makeUserFieldEditable} from "./user-update-contenteditable.js?v=0.3.1";
import {displayChangePasswordModal} from "../update/change-password-modal.html.js?v=0.3.1";
import {displayFlashMessage} from "../../general/page-component/flash-message/flash-message.js?v=0.3.1";
import {submitModalForm} from "../../general/page-component/modal/modal-submit-request.js?v=0.3.1";
import {submitDelete} from "../../general/ajax/submit-delete-request.js?v=0.3.1";
import {createAlertModal} from "../../general/page-component/modal/alert-modal.js?v=0.3.1";
import {loadUserActivities} from "./user-activity/activity-main.js?v=0.3.1";
import {__} from "../../general/general-js/functions.js?v=0.3.1";
import {fetchTranslations} from "../../general/ajax/fetch-translation-data.js?v=0.3.1";
import {submitUpdate} from "../../general/ajax/submit-update-data.js?v=0.3.1";

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
// User language radio buttons
const langRadioButtons = document.querySelectorAll('input[name="language"]');
langRadioButtons.forEach((radio) => {
    radio.addEventListener('change', (e) => {
        submitUpdate({[radio.name]: radio.value}, `users/${userId}`, true)
            .then(r => {
                // Reload page if user changed its own language
                if (userBtn.dataset.isOwnProfile === '1') {
                    location.reload();
                }
            });
    });
});

// Get translations for user update / delete
let wordsToTranslate = [
    __('Are you sure that you want to delete this user?'),
    __('Are you sure that you want to delete your profile?'),
    __('You will be logged out and not be able to log in again.'),
    __('Successfully changed password.'),
];
// Init variable
let translated = Object.fromEntries(wordsToTranslate.map(value => [value, value]));
// Fetch translations and replace translated var
fetchTranslations(wordsToTranslate).then(response => {
    // Fill the var with a JSON of the translated words. Key is the original english words and value the translated one
    translated = response;
});

// Delete button with null safe as it doesn't exist when not privileged
const userBtn = document.querySelector('#delete-user-btn');
userBtn?.addEventListener('click', () => {
    let title = translated['Are you sure that you want to delete this user?'];
    let info = '';
    if (userBtn.dataset.isOwnProfile === '1') {
        title = translated['Are you sure that you want to delete your profile?'];
        info = translated['You will be logged out and not be able to log in again.'];
    }
    createAlertModal(title, info, () => {
        submitDelete(`users/${userId}`, true).then(() => {
            if (userBtn.dataset.isOwnProfile === '1') {
                location.href = `login`;
            } else {
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
    // Submit field change
    submitUpdate({[this.name]: this.value}, `users/${userId}`, true)
        .then(r => {
        });
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
                displayFlashMessage('success', translated['Successfully changed password.']);
            });
    }
});