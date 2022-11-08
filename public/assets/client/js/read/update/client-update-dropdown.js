import {submitClientUpdate} from "./client-update-request.js";

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
            }else{
                // If success is true and select value was empty string, remove dropdown from client personal infos
                if (select.value === '' || select.value === 'NULL'){
                    fieldContainer.parentNode.remove();
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