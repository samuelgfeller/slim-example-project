// For how long should the flash message be visible in ms
const flashMessageTimeBeforeSlideOut = 4000;
// Time for the slide out animation (mobile slide out time is 1s)
const mobileSlideOutAnimationTime = 1000;
// Delay in ms that flash message has to wait before being displayed on load
let displayFlashMessageDelay = 0;
let flashMessageIdCounter = 0;

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
        document.querySelector('#wrapper').insertAdjacentHTML('afterbegin',
            `<aside id="flash-container"></aside>`);
        container = document.querySelector('#flash-container');
    }

    const getFlashIconPath = () => {
        switch (typeName) {
            case 'success':
                // icon.className = typeName;
                return 'assets/general/img/flash-checkmark.svg';
            case 'warning':
                return 'assets/general/img/flash-warning.svg';
            case 'info':
                return 'assets/general/img/flash-info.svg';
            case 'error':
                return 'assets/general/img/flash-error.svg';
        }
    }
    // Add flash message html with unique id
    flashMessageIdCounter += 1; /*Always one more than previous*/
    const flashMessageId = `flash-${flashMessageIdCounter}`;
    container.insertAdjacentHTML('beforeend',
        `<dialog class="flash ${typeName}" id="${flashMessageId}">
                <figure class="flash-fig">
                    <img class="open" src="${getFlashIconPath()}" alt="${typeName}">
                </figure>
                <div class="flash-message"><h3>Flash message</h3><p>${message /*this line has to be on one line*/}</p></div>
                <span class="flash-close-btn">&times;</span>                
            </dialog>`);

    const flash = document.getElementById(flashMessageId);
    showFlashMessage(flash);
}

/**
 * Server side flash messages are loaded into the DOM, but they need to
 * be displayed with the animation and potential delays when multiple
 */
export function displayServerSideFlashMessages(){
    const flashMessages = document.querySelectorAll('.flash');
    for (const flashMessage of flashMessages){
        showFlashMessage(flashMessage);
    }
}

/**
 * Add flash message event listeners and slide flash in
 *
 * @param flash
 */
export function showFlashMessage(flash){
    flash.querySelector('.flash-close-btn').addEventListener('click', slideFlashOut);
    flash.querySelector('.flash-fig').addEventListener('click', slideFlashOut);

    slideInFlashMessage(flash).then(() => {
        setTimeout(() => {
            slideFlashOut.call(flash);
        }, flashMessageTimeBeforeSlideOut);
    });
}

/**
 * Add slide in class with after a potential delay
 *
 * @param flash
 * @return {Promise<unknown>}
 */
function slideInFlashMessage(flash) {
    const slideInTimeoutPromise = new Promise((resolve, reject) => {
        setTimeout(() => {
            flash.classList.add('flash-slide-in');
            resolve();
        }, displayFlashMessageDelay); // https://stackoverflow.com/a/45500721/9013718 (second snippet)
    });

    // Add time before slide out to display delay
    if (window.matchMedia('(min-width: 641px)').matches) {
        // On desktop multiple flash messages can be shown so delay is shorter
        displayFlashMessageDelay += 1000;
    } else {
        // Only one notification at a time should be displayed for mobile so the full display time
        // plus each mobileSlideOutAnimationTime is taken
        displayFlashMessageDelay += flashMessageTimeBeforeSlideOut + mobileSlideOutAnimationTime;
    }
    return slideInTimeoutPromise;
}

/**
 * Slide flash message out of DOM
 * "this" has to be a child of .flash or the object itself
 */
export function slideFlashOut() {
    // "this" is a flash child (close button / figure or element itself)
    const flash = this.closest('.flash');
    flash.className = flash.className.replace('flash-slide-in', "flash-slide-out");
    // Remove flash from dom as it's not needed anymore after slide out animation is done
    setTimeout(() => {
        flash.remove();
    }, mobileSlideOutAnimationTime);
}