import {createModal} from "../../general/js/modal/modal.js?v=0.1";
import {requestDropdownOptions} from "../../general/js/modal/dropdown-request.js?v=0.1";
import {getDropdownAsHtmlOptions, getRadioButtonsAsHtml} from "../../general/js/template/template-util.js?v=0.1";
import {displayFlashMessage} from "../../general/js/requestUtil/flash-message.js?v=0.1";

/**
 * Create and display modal box to create a new client
 */
export function displayClientCreateModal() {
    let header = '<h2>Create client</h2>';
    let body = `<div>
<form action="javascript:void(0);" class="wide-modal-form" id="create-client-modal-form">
        <div class="modal-form-input-group">
            <label for="first-name-input">First name</label>
            <input type="text" name="first_name" id="first-name-input" placeholder="Hans" class="form-input" 
            minlength="2" maxlength="100">
        </div>
        <div class="modal-form-input-group">
            <label for="last-name-input">Last name</label>
            <input type="text" name="last_name" id="last-name-input" placeholder="Zimmer" class="form-input" 
            minlength="2" maxlength="100">
        </div>
        <div class="modal-form-input-group">
            <label for="birthdate-input">Birthdate</label>
            <input type="date" name="birthdate" id="birthdate-input" placeholder="15.03.2000" class="form-input">
        </div>
        <div class="modal-form-input-group">
            <label for="location-input">Location</label>
            <input type="text" placeholder="Basel" id="location-input" name="location" class="form-input" minlength="2" 
            maxlength="100">
        </div>
        <div class="modal-form-input-group double-width-modal-form-input-group">
            <label for="create-message-textarea" class="form-label">Main note</label>
            <!-- Name has to be "message" as it's the name used in note validation -->
            <textarea rows="4" cols="50" name="message" id="create-message-textarea" class="form-input"
                      placeholder="Your message here." minlength="0" maxlength="500"></textarea>
        </div>
        <div class="modal-form-input-group" id="client-sex-input-group-div">
            <label>Sex</label><br>
            <!-- Sex radio buttons are added after modal load in client-template-util.js  -->
        </div>
        <div class="modal-form-input-group">
            <label for="phone-input">Phone number</label>
            <input type="text" name="phone" id="phone-input" placeholder="061 422 32 11" class="form-input" 
            minlength="3" maxlength="20">
        </div>
        <div class="modal-form-input-group">
            <label for="email-input">E-Mail</label>
            <input type="text" name="email" id="email-input" placeholder="mail@example.com" class="form-input" 
            maxlength="254">
        </div>
        <div class="modal-form-input-group">
            <label for="assigned-user-select">Assigned user</label>
            <select name="user_id" class="form-select" id="assigned-user-select">
                <!-- Dropdown options loaded afterwards -->
            </select>
        </div>
        <div class="modal-form-input-group">
            <label for="client-status-select">Status</label>
            <select name="client_status_id" id="client-status-select" class="form-select">
            <!-- Dropdown options loaded afterwards -->
            </select>
        </div>
    </div>`;
    let footer = `<button type="button" id="client-create-submit-btn" class="submit-btn modal-submit-btn">Create client
    </button></form>
    <div class="clearfix">
    </div>`;
    document.getElementById('client-wrapper').insertAdjacentHTML('afterend', '<div id="create-client-div"></div>');
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