import {createModal} from "../../general/page-component/modal/modal.js?v=1.0.0";
import {requestDropdownOptions} from "../../general/page-component/modal/dropdown-request.js?v=1.0.0";
import {getDropdownAsHtmlOptions, getRadioButtonsAsHtml} from "../../general/template/template-util.js?v=1.0.0";
import {displayFlashMessage} from "../../general/page-component/flash-message/flash-message.js?v=1.0.0";
import {__} from "../../general/general-js/functions.js?v=1.0.0";
import {fetchTranslations} from "../../general/ajax/fetch-translation-data.js?v=1.0.0";

// List of words that are used in modal box and need to be translated
let wordsToTranslate = [
    __('Create client'),
    __('First name'),
    __('Last name'),
    __('Birthdate'),
    __('Location'),
    __('Main note'),
    __('Sex'),
    __('Phone number'),
    __('E-Mail'),
    __('Assigned user'),
    __('Status'),
    __('Main note about client.')
];
// Init translated var by populating it with english values as a default so that all keys are existing
let translated = Object.fromEntries(wordsToTranslate.map(value => [value, value]));
// Fetch translations and replace translated var
fetchTranslations(wordsToTranslate).then(response => {
    // Fill the var with a JSON of the translated words. Key is the original english words and value the translated one
    translated = response;
});

/**
 * Create and display modal box to create a new client
 */
export function displayClientCreateModal() {
    let header = `<h2>${translated['Create client']}</h2>`;
    let body = `<div>
<form action="javascript:void(0);" class="wide-modal-form" id="create-client-modal-form">
        <div class="form-input-div">
            <label for="first-name-input">${translated['First name']}</label>
            <input type="text" name="first_name" id="first-name-input" placeholder="Hans" class="form-input" 
            minlength="2" maxlength="100">
        </div>
        <div class="form-input-div">
            <label for="last-name-input">${translated['Last name']}</label>
            <input type="text" name="last_name" id="last-name-input" placeholder="Zimmer" class="form-input" 
            minlength="2" maxlength="100">
        </div>
        <div class="form-input-div">
            <label for="birthdate-input">${translated['Birthdate']}</label>
            <input type="date" name="birthdate" id="birthdate-input" placeholder="15.03.2000">
        </div>
        <div class="form-input-div">
            <label for="location-input">${translated['Location']}</label>
            <input type="text" placeholder="Basel" id="location-input" name="location" minlength="2" 
            maxlength="100">
        </div>
        <div class="form-input-div double-width-form-input-div">
            <label for="create-message-textarea" class="form-label">${translated['Main note']}</label>
            <!-- Name has to be "message" as it's the name used in note validation -->
            <textarea rows="4" cols="50" name="message" id="create-message-textarea"
                      placeholder="${translated['Main note about client.']}" minlength="0" maxlength="500"></textarea>
        </div>
        <div class="form-input-div" id="client-sex-input-group-div">
            <label>${translated['Sex']}</label><br>
            <!-- Sex radio buttons are added after modal load below in addClientDropdownOptionsToCreateModal() -->
        </div>
        <div class="form-input-div">
            <label for="phone-input">${translated['Phone number']}</label>
            <input type="text" name="phone" id="phone-input" placeholder="061 422 32 11" minlength="3" maxlength="20">
        </div>
        <div class="form-input-div">
            <label for="email-input">${translated['E-Mail']}</label>
            <input type="text" name="email" id="email-input" placeholder="mail@example.com" maxlength="254">
        </div>
        <div class="form-input-div">
            <label for="assigned-user-select">${translated['Assigned user']}</label>
            <select name="user_id" id="assigned-user-select">
            <option value=""></option>
                <!-- Dropdown options loaded afterwards -->
            </select>
        </div>
        <div class="form-input-div">
            <label for="client-status-select">${translated['Status']}</label>
    <select name="client_status_id" id="client-status-select">
    <!-- Dropdown options loaded afterwards -->
    </select>
    </div>
    </div>`;
    let footer = `<button type="button" id="client-create-submit-btn" class="submit-btn">
${translated['Create client']}
    </button></form>
    <div class="clearfix">
    </div>`;
    document.getElementById('client-list-wrapper').insertAdjacentHTML('afterend', '<div id="create-client-div"></div>');
    let container = document.getElementById('create-client-div');
    createModal(header, body, footer, container, true);

    // Load dropdown options into client create modal
    requestDropdownOptions('clients').then((dropdownOptions) => {
        addClientDropdownOptionsToCreateModal(dropdownOptions);
    });
}

/**
 * Render loaded dropdown options and radio buttons to create modal form
 *
 * @param dropdownOptions
 */
function addClientDropdownOptionsToCreateModal(dropdownOptions) {
    if (dropdownOptions.hasOwnProperty('users') && dropdownOptions.hasOwnProperty('statuses')
        && dropdownOptions.hasOwnProperty('sexes')
    ) {
        let assignedUserOptions = getDropdownAsHtmlOptions(dropdownOptions.users);
        document.getElementById('assigned-user-select').insertAdjacentHTML("beforeend", assignedUserOptions);
        let clientStatusDropdown = getDropdownAsHtmlOptions(dropdownOptions.statuses);
        document.getElementById('client-status-select').insertAdjacentHTML('beforeend', clientStatusDropdown);
        let clientSexRadioButtons = getRadioButtonsAsHtml(dropdownOptions.sexes, 'sex');
        document.getElementById('client-sex-input-group-div').insertAdjacentHTML('beforeend', clientSexRadioButtons);
    } else {
        displayFlashMessage('error', 'Something went wrong while loading dropdown options.')
    }
}
