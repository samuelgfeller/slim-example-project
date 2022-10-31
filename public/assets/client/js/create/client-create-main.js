import {basePath} from "../../../general/js/config.js";
import {displayClientCreateModal} from "./client-creation.js";
import {loadClientDropdownOptions} from "../client-util.js";
import {getDropdownAsHtmlOptions, getRadioButtonsAsHtml} from "../templates/client-template-util.js";


document.getElementById('create-client-btn').addEventListener('click', e => {
    displayClientCreateModal();
    // load dropdown options into client create modal
    loadClientDropdownOptions((dropdownOptions) => {
        let assignedUserOptions = getDropdownAsHtmlOptions(dropdownOptions.users);
        document.getElementById('assigned-user-select').insertAdjacentHTML("beforeend", assignedUserOptions);
        let clientStatusDropdown = getDropdownAsHtmlOptions(dropdownOptions.statuses);
        document.getElementById('client-status-select').insertAdjacentHTML('beforeend', clientStatusDropdown);
        let clientSexRadioButtons = getRadioButtonsAsHtml(dropdownOptions.sexes, 'sex');
        document.getElementById('client-sex-input-group-div').insertAdjacentHTML('beforeend', clientSexRadioButtons);
    });
});


// document.addEventListener('keydown', function (e) {
//     // When user focuses the card with the keyboard (tab or arrow keys)
//     const card = e.target.closest('.client-profile-card');
//     // Fire click event when Enter or space bar is pressed
//     if (card && (e.key === 'Enter' || e.key === ' ')) {
//         card.click();
//     }
// });

/**
 * Show client modal loader
 */
function showClientModalLoader() {
    document.getElementById('modal-footer').insertAdjacentHTML('afterbegin',
        '<div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div>');
    let submitBtn = document.getElementsByClassName('modal-submit-btn')[0];
    submitBtn.classList.add('modal-submit-btn-loading');
    submitBtn.disabled = true;
}

/**
 * Hide client modal loader
 */
function hideClientModalLoader() {
    document.getElementsByClassName('lds-ellipsis')[0].remove();
    let submitBtn = document.getElementsByClassName('modal-submit-btn')[0];
    submitBtn.classList.remove('modal-submit-btn-loading');
    submitBtn.disabled = false;
}

