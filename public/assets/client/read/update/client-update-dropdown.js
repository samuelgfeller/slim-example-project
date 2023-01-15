import {
    addIconToAvailableDiv,
    removeIconFromAvailableDiv,
    showPersonalInfoContainerIfHidden
} from "../client-read-personal-info.js?v=0.2.0";
import {submitUpdate} from "../../../general/ajax/submit-update-data.js?v=0.2.0";

/**
 * Make personal info field editable by adding a dropdown
 */
export function makeFieldSelectValueEditable() {
    return new Promise(function (resolve, reject) {
        let editIcon = this;
        let fieldContainer = this.parentNode;
        let select = fieldContainer.querySelector('select');
        let span = fieldContainer.querySelector('span');

        // Show personal info container if hidden because it was previously empty
        showPersonalInfoContainerIfHidden();

        showEditableSelect(editIcon, select, span);
        // Focus select to catch focusout if click outside
        select.focus();

        let alreadySubmittedByChangeEvent = false;
        const editableDropdownFieldUpdateEventHandler = (event) => {
            // To catch both focusout (to save and hide dropdown field when no change was made) and change but not
            // submit 2 requests each time, alreadySubmittedByChangeEvent is set to true when event listener picked up "change"
            if (alreadySubmittedByChangeEvent === false || event.type === 'change') {
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
                    resolve(select.value);
                }).catch(responseJson => {
                    // Re enable editable select on error
                    showEditableSelect(editIcon, select, span);
                    reject();
                });

                alreadySubmittedByChangeEvent = false;
            }
            // Remove event listeners if there were any (otherwise they are added multiple times making multiple requests for
            // each change because this function gets executed on each click. A bit better would be to attach event listeners only
            // once at the correct place, but I don't have time for this right now)
            select?.removeEventListener('change', editableDropdownFieldUpdateEventHandler);
            select?.removeEventListener('focusout', editableDropdownFieldUpdateEventHandler);
        };

        // Add event listeners
        select?.addEventListener('change', () => {
            alreadySubmittedByChangeEvent = true;
        });
        select?.addEventListener('change', editableDropdownFieldUpdateEventHandler);
        select?.addEventListener('focusout', editableDropdownFieldUpdateEventHandler);
    }.bind(this));
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