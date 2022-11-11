/**
 * Display form error message
 *
 * @param fieldName
 * @param errorMessage
 * @param {null|string} domFieldId css id of dom field the fail is about in case fieldName is not unique
 * */
export function displayValidationErrorMessage(fieldName, errorMessage, domFieldId = null) {
    let field;
    if (domFieldId !== null) {
        field = document.querySelector('#' + domFieldId);
    } else {
        field = document.querySelector(`[name="${fieldName}"]`);
    }
    if (field === null) {
        // Contenteditable field
        field = document.querySelector(`[data-name="${fieldName}"]`);
    }
    // console.log(domFieldId, field);
    if (field !== null) {
        // If field is a checkbox, the error message placement is a bit different
        if (field.hasAttribute('type') && ['checkbox', 'radio'].includes(field.type)) {
            let radioInputs = document.querySelectorAll(`[name="${fieldName}"]`);
            // field is last label-input radio group and validation error message should be displayed below it
            field = radioInputs[radioInputs.length - 1].parentNode;
        } else {
            // Only add invalid input class to field if not checkbox or radio
            field.classList.add('invalid-input');
        }
        // Remove any existing message in case there was one
        // (this is an additional for when this function is called not from the handleFail() that removes previous error msg)
        field.parentNode.querySelector('strong.err-msg')?.remove();
        field.insertAdjacentHTML('afterend', `<strong class="err-msg">${errorMessage}</strong>`);
        let label = field.parentNode.querySelector('label');
        if (label !== null) {
            label.classList.add('invalid-input');
        }
    }
}

