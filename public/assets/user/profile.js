let firstNameEditIco = document.getElementById('edit-first-name-ico');
firstNameEditIco.addEventListener('click', function () {
    let firstNameValSpan = document.getElementById('first-name-val');
    let inputName = 'first_name';
    let valueParent = replaceValueWithInput(firstNameValSpan, firstNameEditIco, 'first-name-input', inputName);
    createSubmitBtn(valueParent, 'first-name-submit', inputName);
});

let surnameEditIco = document.getElementById('edit-surname-ico');
surnameEditIco.addEventListener('click', function () {
    let surnameValSpan = document.getElementById('surname-val');
    let inputName = 'surname';
    let valueParent = replaceValueWithInput(surnameValSpan, surnameEditIco, 'surname-input', inputName);
    createSubmitBtn(valueParent, 'surname-submit', inputName);
});

let emailEditIco = document.getElementById('edit-email-ico');
emailEditIco.addEventListener('click', function () {
    let emailValSpan = document.getElementById('email-val');
    let inputName = 'email';
    let valueParent = replaceValueWithInput(emailValSpan, emailEditIco, 'email-input', inputName);
    createSubmitBtn(valueParent, 'email-submit', inputName);
});


// Functions

/**
 * This function replaces the profile values (first name,
 * surname, email address) to an input field with a submit button next to it.
 *
 * @param valueSpan <span> DOM-element containing the value
 * @param editIcon <img> DOM-element being the edit icon
 * @param inputId id of input field for label and submit event
 * @param inputName name of input field for submit action
 * @return valueParent Parent of profile value DOM-element
 */
function replaceValueWithInput(valueSpan, editIcon, inputId, inputName) {
    let valueString = valueSpan.innerText;
    // Create input type text
    let valueInputElement = document.createElement('input');
    valueInputElement.type = 'text';
    valueInputElement.className = 'form-input';
    valueInputElement.value = valueString;
    valueInputElement.size = valueString.length + 7;
    valueInputElement.id = inputId;
    valueInputElement.name = inputName;
    // Replace with span
    let valueParent = valueSpan.parentNode;
    valueParent.appendChild(valueInputElement);
    valueParent.removeChild(valueSpan);
    // Put the cursor in the input field
    valueInputElement.focus();
    // Hide icon
    editIcon.style.display = 'none';

    return valueParent;
}

/**
 * Creates a submit btn to save changes
 *
 * @param valueParent parent element of a profile value
 * @param submitBtnId id of element to create
 * @param inputName name attribut of input field (key of the element to change)
 */
function createSubmitBtn(valueParent, submitBtnId, inputName) {
    let valueSubmitImg = document.createElement('img');
    valueSubmitImg.src = 'assets/general/img/thin-checkmark.svg';
    valueSubmitImg.id = submitBtnId;
    valueSubmitImg.className = 'profile-value-submit-icon cursor-pointer';
    valueParent.appendChild(valueSubmitImg);

    // Add event listener
    valueSubmitImg.addEventListener('click', function () {
        submitValueChange(submitBtnId, inputName);
    });
}

/**
 * After click on submit the server request has to be done
 */
function submitValueChange(submitBtnId, inputName) {
    let inputElement = document.querySelector("[name='" + inputName + "']");
    let xHttp = new XMLHttpRequest();
    xHttp.onreadystatechange = function () {

        if (xHttp.readyState === XMLHttpRequest.DONE) {
            // Hide submit icon
            document.getElementById(submitBtnId).remove();
            // if (xHttp.status === 200) {
            if (xHttp.getResponseHeader('Content-type') === 'application/json') {
                // xHttp.responseText
                if (xHttp.status !== 200) {
                    handleFail(xHttp);
                } else {
                    createFlashMessage('success', 'Successfully changed ' + inputName);
                }
            } else {
                handleFail(xHttp);
            }

        }
    };
    // Find user id
    let userId = document.getElementById('personal-info-wrapper').dataset.id;
    // Get basepath. Especially useful when developing on localhost/project-name
    let basePath = document.getElementsByTagName('base')[0].getAttribute('href');
    // Not so sure about which url makes more sense. RestAPI would say PUT /user/{id} so I'll go with this
    xHttp.open('PUT', basePath + 'users/' + userId, true);
    xHttp.setRequestHeader("Content-type", "application/json");

    // xHttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    // Data format: "fname=Henry&lname=Ford"
    // inputName in square brackets to be evaluated https://stackoverflow.com/a/11508490/9013718
    xHttp.send(JSON.stringify({[inputName]: inputElement.value}));
}
