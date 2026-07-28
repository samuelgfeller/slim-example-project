import {createModal} from "../../general/page-component/modal/modal.js?v=4.0.2";
import {requestDropdownOptions} from "../../general/page-component/modal/dropdown-request.js?v=4.0.2";
import {getDropdownAsHtmlOptions, getRadioButtonsAsHtml} from "../../general/template/template-util.js?v=4.0.2";
import {displayFlashMessage} from "../../general/page-component/flash-message/flash-message.js?v=4.0.2";
import {addPasswordStrengthCheck} from "../../authentication/password-strength-checker.js?v=4.0.2";
import {__} from "../../general/general-js/functions.js?v=4.0.2";
import {fetchTranslations} from "../../general/ajax/fetch-translation-data.js?v=4.0.2";

/**
 * Create and display modal box to create a new user.
 * In order for the modal box to be translated, the fetchTranslations()
 * must be done loading when calling this function.
 */
export function displayUserCreateModal() {
    let header = `<h2>${__('Create user')}</h2>`;
    let body = `<div>
<form action="javascript:void(0);" class="wide-modal-form" id="create-user-modal-form">
        <div class="form-input-div">
            <label for="first-name-input">${__('First name')}</label>
            <input type="text" name="first_name" id="first-name-input" placeholder="Hans" class="form-input" 
            minlength="2" maxlength="100" required>
        </div>
        <div class="form-input-div">
            <label for="last-name-input">${__('Last name')}</label>
            <input type="text" name="last_name" id="last-name-input" placeholder="Zimmer" class="form-input" 
            minlength="2" maxlength="100" required>
        </div>
        <div class="form-input-div">
            <label for="email-input">${__('E-Mail')}</label>
            <input type="email" name="email" id="email-input" placeholder="mail@example.com" class="form-input" 
            maxlength="254" required autocomplete="off">
        </div>
        <div class="form-input-div" id="user-lang-input-group-div">
            <label>${__('Language')}</label><br>
            <!-- Radio buttons are added after modal load below in addUserDropdownOptionsToCreateModal() -->
        </div>
        <div class="form-input-div" id="password1-input-div">
            <label for="password1-input">${__('New password')}</label>
            <input type="password" name="password" id="password1-input" minlength="3" required 
             autocomplete="new-password" class="form-input">
        </div>
        <div class="form-input-div">
            <label for="password2-input">${__('Repeat new password')}</label>
            <input type="password" name="password2" id="password2-input" minlength="3" required
             autocomplete="new-password" class="form-input">
        </div>
        <div class="form-input-div">
            <label for="user-status-select">${__('Status')}</label>
            <select name="status" class="form-select" id="user-status-select" required>
                <!-- Dropdown options loaded afterwards -->
            </select>
        </div>
        <div class="form-input-div">
            <label for="user-role-select">${__('User role')}</label>
            <select name="user_role_id" id="user-role-select" class="form-select" required>
            <!-- Dropdown options loaded afterwards -->
            </select>
        </div>
    </div>`;
    let footer = `<button type="button" id="user-create-submit-btn" class="submit-btn modal-submit-btn">
${__('Create user')}
    </button></form>
    <div class="clearfix">
    </div>`;
    document.querySelector('body').insertAdjacentHTML('afterbegin', '<div id="create-user-div"></div>');
    let container = document.getElementById('create-user-div');
    createModal(header, body, footer, container, true);
// Load dropdown options into client create modal
    requestDropdownOptions('users').then((dropdownOptions) => {
        addUserDropdownOptionsToCreateModal(dropdownOptions);
    });
// Display password as unsafe if breached and disable submit btn if passwords don't match
    addPasswordStrengthCheck();
}

/**
 * Render loaded dropdown options and radio buttons to create modal form
 * hardcoded default user role 4 newcomer and status unverified.
 *
 * @param dropdownOptions
 */
function addUserDropdownOptionsToCreateModal(dropdownOptions) {
    if (dropdownOptions.hasOwnProperty('userRoles')
        && dropdownOptions.hasOwnProperty('statuses')
        && dropdownOptions.hasOwnProperty('languages')
    ) {
        let userRoleOptions = getDropdownAsHtmlOptions(dropdownOptions.userRoles, 4);
        document.getElementById('user-role-select').insertAdjacentHTML("beforeend", userRoleOptions);
        let statusOptions = getDropdownAsHtmlOptions(dropdownOptions.statuses, 'unverified');
        document.getElementById('user-status-select').insertAdjacentHTML('beforeend', statusOptions);
        let languageRadioButtons = getRadioButtonsAsHtml(dropdownOptions.languages, 'language');
        document.getElementById('user-lang-input-group-div').insertAdjacentHTML('beforeend', languageRadioButtons);
    } else {
        displayFlashMessage('error', 'Something went wrong while loading dropdown options.')
    }
}
