import {submitClientUpdate} from "./client-update-request.js";
import {
    addIconToAvailableDiv,
    removeIconFromAvailableDiv
} from "../client-read-personal-info.js";

export function makeFieldSelectValueEditable() {
    let editIcon = this;
    let fieldContainer = this.parentNode;
    let select = fieldContainer.querySelector('select');
    let span = fieldContainer.querySelector('span');

    showEditableSelect(editIcon, select, span);
    // Focus select to catch focusout if click outside
    select.focus();
    select?.addEventListener('change', () => {
        removeSelectAndShowSpan(editIcon, select, span);
        submitClientUpdate(select.name, select.value).then(successData => {
            if (successData.success === false) {
                // Re enable editable select on error
                showEditableSelect(editIcon, select, span);
            } else {
                let availableIcon = document.querySelector('#add-client-personal-info-div img[alt="' + select.name + '"]');
                // If success is true and select value was empty string, remove dropdown from client personal infos
                if ((select.value === '' || select.value === 'NULL') && fieldContainer.dataset.hideIfEmpty === 'true') {
                    addIconToAvailableDiv(availableIcon, fieldContainer.parentNode);
                }else if(fieldContainer.dataset.hideIfEmpty === 'true' && availableIcon !== null) {
                    removeIconFromAvailableDiv(availableIcon);
                }
            }
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