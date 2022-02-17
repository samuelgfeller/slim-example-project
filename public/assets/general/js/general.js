
/**
 * Create and display flash message from the client side
 * Display server side flash: flash-messages.html.php
 *
 * @param {string} typeName (success | error | warning | info)
 * @param {string} message flash message content
 */
function createFlashMessage(typeName, message){
    // Wrapper
    let container = document.getElementById("flash-container");
    // If it isn't "undefined" and it isn't "null", then it exists.
    if(typeof(container) === 'undefined' || container === null) {
        // console.log(wrapper === null);
        container = document.createElement('aside');
        container.id = 'flash-container';
        document.appendChild(container);
    }

    // First child: dialog
    let dialog = document.createElement("dialog");
    dialog.className = 'flash ' + typeName;
    // Append dialog
    container.appendChild(dialog);

    // Second child: figure
    let figure = document.createElement('figure');
    figure.className = 'flash-fig';
    // Append figure to dialog
    dialog.appendChild(figure);

    // Third child: img
    let icon = document.createElement('img');
    icon.className = 'open';
    switch (typeName){
        case 'success':
            // icon.className = typeName;
            icon.src = 'assets/general/img/checkmark.svg';
            icon.alt = 'success';
            break;
        case 'warning':
            icon.src = 'assets/general/img/warning-icon.svg';
            icon.alt = 'success';
            break;
        case 'info':
            icon.src = 'assets/general/img/info-icon.svg';
            icon.alt = 'success';
            break;
        case 'error':
            icon.src = 'assets/general/img/cross-icon.svg';
            icon.alt = 'error';
            break;
    }
    figure.appendChild(icon);

    // First dialog child: flash message content
    let flashMessageDiv = document.createElement('div');
    flashMessageDiv.className = 'flash-message';
    dialog.appendChild(flashMessageDiv);

    // First flash message content child: header
    let flashMessageHeader = document.createElement('h3');
    flashMessageHeader.textContent = 'Hey'; // Replaced by css
    flashMessageDiv.appendChild(flashMessageHeader);

    // Second flash message content child: message content
    let flashMessageContent = document.createElement('p');
    flashMessageContent.innerHTML = message;
    flashMessageDiv.appendChild(flashMessageContent);

    // Second dialog child: close flash button
    let closeBtn = document.createElement('span');
    closeBtn.className = 'flash-close-btn';
    closeBtn.innerHTML = "&times";
    dialog.appendChild(closeBtn);

    // Make it visible to the user
    showFlashMessages();
}

/**
 * If a request fails this function can be called which gives the user
 * information about which error it is
 *
 * @param {XMLHttpRequest} xhr
 */
function handleFail(xhr){
    let errorMsg = 'Request failed. Please try again.';

    if (xhr.status === 401 || xhr.status === '401'){
        // Overwriting general error message to unauthorized
        errorMsg = 'Access denied please authenticate and try again';
    }
    if (xhr.status === 403 || xhr.status === '403'){
        errorMsg = 'Forbidden. You do not have access to this area or function';
    }

    if (xhr.status === 404 || xhr.status === '404'){
        errorMsg = 'Page not found!';
    }

    if (xhr.status === 500 || xhr.status === '500'){
        errorMsg = 'Internal server error';
    }

    // Add error messages if they are given by the backend
    // if(typeof xhr.statusText !== 'undefined' ){
        // If we know the error message we can add it to the error popup
        // errorMsg += '<br>Message: '+xhr.statusText;
    // }

    errorMsg += '<br>Code: '+ xhr.status + ' ' + xhr.statusText;

    // If validation error ignore the default message and create specific one
    if (xhr.status === 422){
        errorMsg = '';
        let json = xhr.response;
        let validationErrors = JSON.parse(json);
        // Best foreach loop method according to https://stackoverflow.com/a/9329476/9013718
        for (const error of validationErrors.data.errors){
            errorMsg += error.message + ' for <b>' + error.field.replace(/[^a-zA-Z0-9 ]/g, ' ') + '</b><br>';
        }
    }

    // Output error to user
    createFlashMessage('error', errorMsg);
}

/**
 * Check html validity of form and display browser default error
 *
 * Source: https://stackoverflow.com/a/11867013/9013718
 *
 * @param formId
 */
// function formIsValid(formId){
//     if(!document.getElementById(formId).checkValidity()) {
//         // If the form is invalid, submit it. The form won't actually submit;
//         // this will just cause the browser to display the native HTML5 error messages.
//         $('<input type="submit">').hide().appendTo($('#'+formId)).click().remove();
//         return false;
//     }
//     return true;
// }