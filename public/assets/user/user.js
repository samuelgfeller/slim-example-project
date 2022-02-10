
let firstNameEditIco = document.getElementById('edit-first-name-ico');
firstNameEditIco.addEventListener('click', function () {
    let firstNameValSpan = document.getElementById('first-name-val');
    replaceValueWithInput(firstNameValSpan, firstNameEditIco);
});

let surnameEditIco = document.getElementById('edit-surname-ico');
surnameEditIco.addEventListener('click', function () {
    let surnameValSpan = document.getElementById('surname-val');
    replaceValueWithInput(surnameValSpan, surnameEditIco);
});

let emailEditIco = document.getElementById('edit-email-ico');
emailEditIco.addEventListener('click', function () {
    let emailValSpan = document.getElementById('email-val');
    replaceValueWithInput(emailValSpan, emailEditIco);
});



/**
 * This function replaces the profile values (first name,
 * surname, email address) to an input field with a submit button next to it.
 *
 * @param valueSpan <span> DOM-element containing the value
 * @param editIcon <img> DOM-element being the edit icon
 */
function replaceValueWithInput(valueSpan, editIcon){
    let valueString = valueSpan.innerText;
    // Create input type text
    let valueInputElement = document.createElement('input');
    valueInputElement.type = 'text';
    valueInputElement.className = 'form-input';
    valueInputElement.value = valueString;
    valueInputElement.size = valueString.length + 13;
    // Replace with span
    valueSpan.parentNode.appendChild(valueInputElement);
    valueSpan.parentNode.removeChild(valueSpan);
    // Put the cursor in the input field
    valueInputElement.focus();
    // Hide icon
    editIcon.style.display = 'none';
}