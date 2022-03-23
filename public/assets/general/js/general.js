
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
    let errorMsg = 'Code: '+ xhr.status + ' ' + xhr.statusText;

    if (xhr.status === 401){
        // Overwriting general error message to unauthorized
        errorMsg += '<br>Access denied please log in and try again.';
    }
    if (xhr.status === 403){
        errorMsg += '<br>Forbidden. You do not have access to this area or function';
    }

    if (xhr.status === 500){
        errorMsg += '<br>Please try again and then <a href="mailto:contact@samuel-gfeller.ch">contact me</a>.';
    }

    // If validation error ignore the default message and create specific one
    if (xhr.status === 422) {
        if (xhr.getResponseHeader('Content-type') === 'application/json') {
            errorMsg = '';
            let json = xhr.response;
            let validationErrors = JSON.parse(json);
            // Best foreach loop method according to https://stackoverflow.com/a/9329476/9013718
            for (const error of validationErrors.data.errors) {
                errorMsg += error.message + ' for <b>' + error.field.replace(/[^a-zA-Z0-9 ]/g, ' ') + '</b><br>';
            }
        } else{
            // Default error message when server returns 422 but not json
            errorMsg = 'Validation error. Something could not have been validate on the server.';
        }
    }

    // Output error to user
    createFlashMessage('error', errorMsg);
}