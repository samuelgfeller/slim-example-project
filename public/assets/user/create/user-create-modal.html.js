import {createModal} from "../../general/page-component/modal/modal.js?v=0.4.2";
import {requestDropdownOptions} from "../../general/page-component/modal/dropdown-request.js?v=0.4.2";
import {getDropdownAsHtmlOptions, getRadioButtonsAsHtml} from "../../general/template/template-util.js?v=0.4.2";
import {displayFlashMessage} from "../../general/page-component/flash-message/flash-message.js?v=0.4.2";
import {addPasswordStrengthCheck} from "../../authentication/password-strength-checker.js?v=0.4.2";
import {__} from "../../general/general-js/functions.js?v=0.4.2";
import {fetchTranslations} from "../../general/ajax/fetch-translation-data.js?v=0.4.2";

// List of words that are used in modal box and need to be translated
let wordsToTranslate = [
    __('Create user'),
    __('First name'),
    __('Last name'),
    __('E-Mail'),
    __('Language'),
    __('New password'),
    __('Repeat new password'),
    __('Status'),
    __('User role')
];
// Init translated var by populating it with english values as a default so that all keys are existing
let translated = Object.fromEntries(wordsToTranslate.map(value => [value, value]));
// Fetch translations and replace translated var
fetchTranslations(wordsToTranslate).then(response => {
    // Fill the var with a JSON of the translated words. Key is the original english words and value the translated one
    translated = response;
});

/**
 * Create and display modal box to create a new user.
 * In order for the modal box to be translated, the fetchTranslations()
 * must be done loading when calling this function.
 */
export function displayUserCreateModal() {
    let header = `<h2>${translated['Create user']}</h2>`;
    let body = `<div>
<form action="javascript:void(0);" class="wide-modal-form" id="create-user-modal-form">
        <div class="form-input-div">
            <label for="first-name-input">${translated['First name']}</label>
            <input type="text" name="first_name" id="first-name-input" placeholder="Hans" class="form-input" 
            minlength="2" maxlength="100" required>
        </div>
        <div class="form-input-div">
            <label for="last-name-input">${translated['Last name']}</label>
            <input type="text" name="last_name" id="last-name-input" placeholder="Zimmer" class="form-input" 
            minlength="2" maxlength="100" required>
        </div>
        <div class="form-input-div">
            <label for="email-input">${translated['E-Mail']}</label>
            <input type="email" name="email" id="email-input" placeholder="mail@example.com" class="form-input" 
            maxlength="254" required autocomplete="off">
        </div>
        <div class="form-input-div" id="user-lang-input-group-div">
            <label>${translated['Language']}</label><br>
            <!-- Radio buttons are added after modal load below in addUserDropdownOptionsToCreateModal() -->
        </div>
        <div class="form-input-div" id="password1-input-div">
            <label for="password1-input">${translated['New password']}</label>
            <input type="password" name="password" id="password1-input" minlength="3" required 
             autocomplete="new-password" class="form-input">
        </div>
        <div class="form-input-div">
            <label for="password2-input">${translated['Repeat new password']}</label>
            <input type="password" name="password2" id="password2-input" minlength="3" required
             autocomplete="new-password" class="form-input">
        </div>
        <div class="form-input-div">
            <label for="user-status-select">${translated['Status']}</label>
            <select name="status" class="form-select" id="user-status-select" required>
                <!-- Dropdown options loaded afterwards -->
            </select>
        </div>
        <div class="form-input-div">
            <label for="user-role-select">${translated['User role']}</label>
            <select name="user_role_id" id="user-role-select" class="form-select" required>
            <!-- Dropdown options loaded afterwards -->
            </select>
        </div>
    </div>`;
    let footer = `<button type="button" id="user-create-submit-btn" class="submit-btn modal-submit-btn">
${translated['Create user']}
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
