import {makeUserFieldEditable} from "./user-update-contenteditable.js";
import {submitUserUpdate} from "../update/user-update-request.js";

document.querySelector('#edit-first-name-btn')?.addEventListener('click', makeUserFieldEditable);
document.querySelector('#edit-last-name-btn')?.addEventListener('click', makeUserFieldEditable);
document.querySelector('#edit-email-btn')?.addEventListener('click', makeUserFieldEditable);

// User dropdown change event listeners
const statusSelect = document.querySelector('select[name="status"]:not([disabled])');
statusSelect?.addEventListener('change', () => {
    submitUserUpdate(statusSelect.name, statusSelect.value).then();
});
const userRoleSelect = document.querySelector('select[name="user_role_id"]:not([disabled])');
userRoleSelect?.addEventListener('change', () => {
    submitUserUpdate(userRoleSelect.name, userRoleSelect.value).then();
});



// Display all edit icons if touch screen
if ('ontouchstart' in window || navigator.msMaxTouchPoints) {
    let editIcons = document.querySelectorAll('.contenteditable-edit-icon');
    for (let editIcon of editIcons) {
        editIcon.classList.toggle('always-displayed-icon');
    }
}
