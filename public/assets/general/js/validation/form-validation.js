/**
 * Display form error message
 *
 * @param fieldName
 * @param errorMessage
 */
export function displayFormInputErrorMessage(fieldName, errorMessage){
    let field = document.querySelector(`[name="${fieldName}"]`);
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
    field.parentNode.querySelector('label').classList.add('invalid-input');
}