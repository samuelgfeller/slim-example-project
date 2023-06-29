// For how long should the flash message be visible in ms
const flashMessageTimeBeforeSlideOut = 6000;
// Time for the slide out CSS animation (mobile slide out time is 1s)
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
        document.querySelector('body').insertAdjacentHTML('afterbegin',
            `<aside id="flash-container"></aside>`);
        container = document.querySelector('#flash-container');
    }

    const getFlashIconPath = () => {
        switch (typeName) {
            case 'success':
                // icon.className = typeName;
                return 'assets/general/page-component/flash-message/img/flash-checkmark.svg';
            case 'warning':
                return 'assets/general/page-component/flash-message/img/flash-warning.svg';
            case 'info':
                return 'assets/general/page-component/flash-message/img/flash-info.svg';
            case 'error':
                return 'assets/general/page-component/flash-message/img/flash-error.svg';
        }
    }
    // Add flash message html with unique id
    flashMessageIdCounter += 1; /*Always one more than previous*/
    const flashMessageId = `flash-${flashMessageIdCounter}`;
    // Same HTML than flash-messages.html.php
    container.insertAdjacentHTML('beforeend',
        `<dialog class="flash ${typeName}" id="${flashMessageId}">
                <figure class="flash-fig" >
                    <img class="open" draggable="false" src="${getFlashIconPath()}" alt="${typeName}">
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
export function displayServerSideFlashMessages() {
    const flashMessages = document.querySelectorAll('.flash');
    for (const flashMessage of flashMessages) {
        showFlashMessage(flashMessage);
    }
}

/**
 * Add flash message event listeners and slide flash in
 *
 * @param flash
 */
export function showFlashMessage(flash) {
    flash.querySelector('.flash-close-btn').addEventListener('click', slideFlashOut);
    flash.querySelector('.flash-fig').addEventListener('click', slideFlashOut);

    flash.addEventListener('touchmove', dragFlashOnMouseMove);
    // As mouse up event is on document it is triggered on every mouse up calling moveFlashOutOnItsOwn
    // The variable mouseDownOnFlash indicates that there was previously a mouse down on the flash message
    let mouseDownOnFlash = false;
    flash.addEventListener('mousedown', (e) => {
        // Not if target is p as user may want to select the text
        if (e.target.tagName !== 'P') {
            flash.addEventListener('mousemove', dragFlashOnMouseMove);
            mouseDownOnFlash = true;
        }
    });
    // Mouseup event on document as user may click on flash and then move cursor out, lift the button and come back on it
    document.addEventListener('mouseup', () => {
        // Only call event handler if there was previously a mouse click on the flash message
        if (mouseDownOnFlash) {
            flash.removeEventListener('mousemove', dragFlashOnMouseMove);
            moveFlashOutOnItsOwn.call(flash);
            // Reset mouse down on flash bool
            mouseDownOnFlash = false;
        }
    });

    flash.addEventListener('touchstart', e => {
        // Prevent scroll especially on mobile
        e.preventDefault();
        e.stopPropagation();
    })
    flash.addEventListener('touchend', moveFlashOutOnItsOwn);

    // Slide flash message in and then out after the given time
    slideInFlashMessage(flash).then(() => {
        setTimeout(() => {
            slideFlashOut.call(flash);
        }, flashMessageTimeBeforeSlideOut);
    }).catch((error) => {
        console.error(error);
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
        displayFlashMessageDelay += 2000;
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
        // Reset flash message slide in delay after it slid out
        displayFlashMessageDelay = 0;
    }, mobileSlideOutAnimationTime);
}


// Variables used for the function dragFlashOnMouseMove
let initX = null;
let initY = null;
let movedDistanceX;
let movedDistanceY;
let isMobile;

/**
 * Touch event handler; moves flash with user touch input
 * @param e
 */
function dragFlashOnMouseMove(e) {
    let currX, currY;
    if (e.clientX) {
        currX = e.clientX; // If they exist then use Mouse input
        currY = e.clientY;
    } else {
        currX = e.touches[0].clientX; // Otherwise use touch input
        currY = e.touches[0].clientY;
    }
    if (initX === null || initY === null) {
        initX = currX;
        initY = currY;
    } else {
        movedDistanceX = currX - initX;
        movedDistanceY = currY - initY;
        // console.log('x ' + Math.abs(movedDistanceX), 'y ' + movedDistanceY)
        isMobile = !window.matchMedia('(min-width: 641px)').matches;
        // Only move flash if mobile or to the left if desktop
        if (isMobile || movedDistanceX > 0) {
            // If swipe to the top (negative Y moved distance) more than 20px and not swiped left or right of more than 20
            if (movedDistanceY < -20 && Math.abs(movedDistanceX) < 40) {
                // "this" is the flash
                slideFlashOut.call(this);
            }
            // Move flash with finger only after moved more than 20px to avoid moving it when swiping it upwards
            if (Math.abs(movedDistanceX) > 20) {
                this.style.left = (movedDistanceX) + 'px';
            }
        }
    }
}

let startTime = 0;
let endTime = 0;
let isCurrentlyMovingOut = false;

/**
 * Continue user flash sliding movement without touch
 * "this" is the flash element
 */
function moveFlashOutOnItsOwn() {
    const timeMs = ((endTime - startTime));

    let distanceToMoveOut = movedDistanceX;
    // Slide flash out automatically only if horizontal moved distance is greater than 30px
    if (Math.abs(movedDistanceX) > 20 && // And is mobile or swipe is to the right (and not left)
        (isMobile || movedDistanceX > 0) // And not already moving out
        && isCurrentlyMovingOut === false
    ) {
        isCurrentlyMovingOut = true;
        let moveFlashOutInterval = setInterval(() => {
                // Stop if left value is below -600 or above 600 as it's certain that the flash is outside of viewport
                if ((distanceToMoveOut < -600) || distanceToMoveOut > 600) {
                    clearInterval(moveFlashOutInterval);
                    isCurrentlyMovingOut = false;
                    // Directly remove flash from dom. Slide out animation not needed
                    this.remove();
                }
                // If negative number, one has to be subtracted, otherwise one added
                distanceToMoveOut < 0 ? distanceToMoveOut-- : distanceToMoveOut++;
                // Continue slide out movement automatically at twice the speed of user drag (twice the pixels moved in
                // the same amount of time)
                this.style.left = (distanceToMoveOut) * 2 + 'px';
            }, // Interval is the time the user dragged the flash message divided my the amount of pixels
            // This is the ratio of moved pixels per ms
            timeMs / movedDistanceX);
    }
}
