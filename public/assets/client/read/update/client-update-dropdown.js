import {addIconToAvailableDiv, removeIconFromAvailableDiv} from "../client-read-personal-info.js?v=0.1";
import {submitUpdate} from "../../../general/js/request/submit-update-data.js?v=0.1";

/**
 * Make personal info field editable by adding a dropdown
 */
export function makeFieldSelectValueEditable() {
    let editIcon = this;
    let fieldContainer = this.parentNode;
    let select = fieldContainer.querySelector('select');
    let span = fieldContainer.querySelector('span');

    showEditableSelect(editIcon, select, span);
    // Focus select to catch focusout if click outside
    select.focus();
    select?.addEventListener('change', () => {
        // Done here and not after successful request to indicate to user that change was taken into account
        removeSelectAndShowSpan(editIcon, select, span);

        let clientId = document.getElementById('client-id').value;
        submitUpdate(
            {[select.name]: select.value},
            `clients/${clientId}`,
            `clients/${clientId}`,
        ).then(responseJson => {
            let availableIcon = document.querySelector('#add-client-personal-info-div img[alt="' + select.name + '"]');
            // If success is true and select value was empty string, remove dropdown from client personal infos
            if ((select.value === '' || select.value === 'NULL') && fieldContainer.dataset.hideIfEmpty === 'true') {
                addIconToAvailableDiv(availableIcon, fieldContainer.parentNode);
            } else if (fieldContainer.dataset.hideIfEmpty === 'true' && availableIcon !== null) {
                removeIconFromAvailableDiv(availableIcon);
            }
        }).catch(responseJson => {
            // Re enable editable select on error
            showEditableSelect(editIcon, select, span);
        });
    });

    select?.addEventListener('focusout', () => {
        removeSelectAndShowSpan(editIcon, select, span);
    });
}

function showEditableSelect(editIcon, select, span) {
    editIcon.style.display = 'none';
    select.style.display = 'inline-block';
    span.style.display = 'none';
}

function removeSelectAndShowSpan(editIcon, select, span) {
    select.style.display = 'none';
    span.textContent = select.options[select.selectedIndex].text;
    span.style.display = 'inline-block';
    editIcon.style.display = null;
}