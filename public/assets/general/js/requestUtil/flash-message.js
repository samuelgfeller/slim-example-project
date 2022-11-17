/**
 * Create and display flash message from the client side
 * Display server side flash: flash-messages.html.php
 *
 * @param {string} typeName (success | error | warning | info)
 * @param {string} message flash message content
 */
export function displayFlashMessage(typeName, message) {
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
            icon.src = 'assets/general/img/flash-checkmark.svg';
            icon.alt = 'success';
            break;
        case 'warning':
            icon.src = 'assets/general/img/flash-warning.svg';
            icon.alt = 'success';
            break;
        case 'info':
            icon.src = 'assets/general/img/flash-info.svg';
            icon.alt = 'success';
            break;
        case 'error':
            icon.src = 'assets/general/img/flash-error.svg';
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
 * Display flash messages to user
 *
 * In own function to be run client side after loading
 */
export function showFlashMessages() {
    let flashes = document.getElementsByClassName("flash");
    Array.from(flashes).forEach(function (flash, index) {
        if (index === 0) {
            // Add first without timeout
            flash.className += ' slide-in'
        } else {
            setTimeout(function () {
                flash.className += ' slide-in'
            }, index * 1000); // https://stackoverflow.com/a/45500721/9013718 (second snippet)
        }
        let closeBtn = flash.querySelector('.flash-close-btn');
        closeBtn.addEventListener('click', function () {
            slideFlashOut(flash);
        });
        let flashFig = flash.querySelector('.flash-fig');
        flashFig.addEventListener('click', function () {
            slideFlashOut(flash);
        });

        setTimeout(slideFlashOut, (index * 1000) + 8000, flash);
    });
}

/**
 * Remove flash message after a few seconds of display
 *
 * @param flash
 */
function slideFlashOut(flash) {
    flash.className = flash.className.replace('slide-in', "slide-out");
    // Hide a bit later so that page content can go to its place again
    setTimeout(function () {
        flash.style.display = 'none';
        flash.remove();
    }, 800); // .slide-out animation is 0.9s
}