import {loadClientDropdownOptions} from "../client-util.js";
import {getDropdownAsHtmlOptions, getRadioButtonsAsHtml} from "../templates/client-template-util.js";
import {displayClientCreateModal} from "../templates/client-create-template.js";
import {submitCreateClient} from "./client-create-request.js";

// Init event listeners
document.getElementById('create-client-btn').addEventListener('click', e => {
    displayClientCreateModal();
    // load dropdown options into client create modal
    loadClientDropdownOptions(addClientDropdownOptionsToCreateModal);
});

// Submit form on create button click
document.addEventListener('click', e => {
    // Event delegation as modal is removed and added dynamically
    if (e.target && e.target.id === 'client-create-submit-btn') {
        submitCreateClient();
    }
});

/**
 * Render loaded dropdown options and radio buttons to create modal form
 *
 * @param dropdownOptions
 */
function addClientDropdownOptionsToCreateModal(dropdownOptions) {
    let assignedUserOptions = getDropdownAsHtmlOptions(dropdownOptions.users);
    document.getElementById('assigned-user-select').insertAdjacentHTML("beforeend", assignedUserOptions);
    let clientStatusDropdown = getDropdownAsHtmlOptions(dropdownOptions.statuses);
    document.getElementById('client-status-select').insertAdjacentHTML('beforeend', clientStatusDropdown);
    let clientSexRadioButtons = getRadioButtonsAsHtml(dropdownOptions.sexes, 'sex');
    document.getElementById('client-sex-input-group-div').insertAdjacentHTML('beforeend', clientSexRadioButtons);
}

