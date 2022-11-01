/**
 * Create and display flash message from the client side
 * Display server side flash: flash-messages.html.php
 *
 * @param {string} typeName (success | error | warning | info)
 * @param {string} message flash message content
 */
export function createFlashMessage(typeName, message) {
    // Wrapper
    let container = document.getElementById("flash-container");
    // If it isn't "undefined" and it isn't "null", then it exists.
    if (typeof (container) === 'undefined' || container === null) {
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
    switch (typeName) {
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
