import {createModal} from "../../general/js/modal/modal.js";
import {requestDropdownOptions} from "../../general/js/modal/dropdown-request.js";
import {getDropdownAsHtmlOptions} from "../../general/js/template/template-util.js";
import {displayFlashMessage} from "../../general/js/requests/flash-message.js";

/**
 * Create and display modal box to create a new client
 */
export function displayUserCreateModal() {
    let header = '<h2>Create user</h2>';
    let body = `<div>
<form action="javascript:void(0);" class="wide-modal-form" id="create-user-modal-form">
        <div class="modal-form-input-group">
            <label for="first-name-input">First name</label>
            <input type="text" name="first_name" id="first-name-input" placeholder="Hans" class="form-input" 
            minlength="2" maxlength="100" required>
        </div>
        <div class="modal-form-input-group">
            <label for="last-name-input">Last name</label>
            <input type="text" name="surname" id="last-name-input" placeholder="Zimmer" class="form-input" 
            minlength="2" maxlength="100" required>
        </div>
        <div class="modal-form-input-group">
            <label for="email-input">E-Mail</label>
            <input type="text" name="email" id="email-input" placeholder="mail@example.com" class="form-input" 
            maxlength="254" required>
        </div>
        <div class="modal-form-input-group">
            <label for="password1-inp">New password</label>
            <input type="password" name="password" id="password1-inp" minlength="3" required class="form-input">
        </div>
        <div class="modal-form-input-group">
            <label for="password2-inp">Repeat new password</label>
            <input type="password" name="password2" id="password2-inp" minlength="3" required class="form-input">
        </div>
        <div class="modal-form-input-group">
            <label for="user-status-select">Status</label>
            <select name="status" class="form-select" id="user-status-select" required>
                <!-- Dropdown options loaded afterwards -->
            </select>
        </div>
        <div class="modal-form-input-group">
            <label for="user-role-select">User role</label>
            <select name="user_role_id" id="user-role-select" class="form-select" required>
            <!-- Dropdown options loaded afterwards -->
            </select>
        </div>
    </div>`;
    let footer = `<button type="button" id="user-create-submit-btn" class="submit-btn modal-submit-btn">Create user
    </button></form>
    <div class="clearfix">
    </div>`;
    document.querySelector('body').insertAdjacentHTML('afterbegin', '<div id="create-user-div"></div>');
    let container = document.getElementById('create-user-div');
    createModal(header, body, footer, container);

    // Load dropdown options into client create modal
    requestDropdownOptions('users').then((dropdownOptions) => {
        addUserDropdownOptionsToCreateModal(dropdownOptions);
    });
}

/**
 * Render loaded dropdown options and radio buttons to create modal form
 * Hardcoded default user role 4 newcomer and status unverified
 *
 * @param dropdownOptions
 */
function addUserDropdownOptionsToCreateModal(dropdownOptions) {
    if (dropdownOptions.hasOwnProperty('userRoles') && dropdownOptions.hasOwnProperty('statuses')) {
        let userRoleOptions = getDropdownAsHtmlOptions(dropdownOptions.userRoles, 4);
        document.getElementById('user-role-select').insertAdjacentHTML("beforeend", userRoleOptions);
        let statusOptions = getDropdownAsHtmlOptions(dropdownOptions.statuses, 'unverified');
        document.getElementById('user-status-select').insertAdjacentHTML('beforeend', statusOptions);
    } else {
        displayFlashMessage('error', 'Something went wrong while loading dropdown options.')
    }
}