import {createModal} from "../../general/page-component/modal/modal.js?v=4.0.2";
import {requestDropdownOptions} from "../../general/page-component/modal/dropdown-request.js?v=4.0.2";
import {getDropdownAsHtmlOptions, getRadioButtonsAsHtml} from "../../general/template/template-util.js?v=4.0.2";
import {displayFlashMessage} from "../../general/page-component/flash-message/flash-message.js?v=4.0.2";
import {__} from "../../general/general-js/functions.js?v=4.0.2";

/**
 * Create and display modal box to create a new client
 */
export function displayClientCreateModal() {
    let header = `<h2>${__('Create client')}</h2>`;
    let body = `<div>
<form action="javascript:void(0);" class="wide-modal-form" id="create-client-modal-form">
        <div class="form-input-div">
            <label for="first-name-input">${__('First name')}</label>
            <input type="text" name="first_name" id="first-name-input" placeholder="Hans" class="form-input" 
            minlength="2" maxlength="100">
        </div>
        <div class="form-input-div">
            <label for="last-name-input">${__('Last name')}</label>
            <input type="text" name="last_name" id="last-name-input" placeholder="Zimmer" class="form-input" 
            minlength="2" maxlength="100">
        </div>
        <div class="form-input-div">
            <label for="birthdate-input">${__('Birthdate')}</label>
            <input type="date" name="birthdate" id="birthdate-input" placeholder="15.03.2000">
        </div>
        <div class="form-input-div">
            <label for="location-input">${__('Location')}</label>
            <input type="text" placeholder="Basel" id="location-input" name="location" minlength="2" 
            maxlength="100">
        </div>
        <div class="form-input-div double-width-form-input-div">
            <label for="create-message-textarea" class="form-label">${__('Main note')}</label>
            <!-- Name has to be "message" as it's the name used in note validation -->
            <textarea rows="4" cols="50" name="message" id="create-message-textarea"
                      placeholder="${__('Main note about client.')}" minlength="0" maxlength="500"></textarea>
        </div>
        <div class="form-input-div" id="client-sex-input-group-div">
            <label>${__('Sex')}</label><br>
            <!-- Sex radio buttons are added after modal load below in addClientDropdownOptionsToCreateModal() -->
        </div>
        <div class="form-input-div">
            <label for="phone-input">${__('Phone number')}</label>
            <input type="text" name="phone" id="phone-input" placeholder="061 422 32 11" minlength="3" maxlength="20">
        </div>
        <div class="form-input-div">
            <label for="email-input">${__('E-Mail')}</label>
            <input type="text" name="email" id="email-input" placeholder="mail@example.com" maxlength="254">
        </div>
        <div class="form-input-div">
            <label for="assigned-user-select">${__('Assigned user')}</label>
            <select name="user_id" id="assigned-user-select">
            <option value=""></option>
                <!-- Dropdown options loaded afterwards -->
            </select>
        </div>
        <div class="form-input-div">
            <label for="client-status-select">${__('Status')}</label>
    <select name="client_status_id" id="client-status-select">
    <!-- Dropdown options loaded afterwards -->
    </select>
    </div>
    </div>`;
    let footer = `<button type="button" id="client-create-submit-btn" class="submit-btn">
${__('Create client')}
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
