window.addEventListener("load", function (event) {
    /** Class with no animation on page load */
    let elements = document.getElementsByClassName("no-animation-on-page-load");
    // elements is a HTMLCollection and does not have forEach method. It has to be converted as array before
    Array.from(elements).forEach(function (element) {
        element.classList.remove("no-animation-on-page-load");
    });

    /** Page height - mobile*/
        // vh without scrollbar on mobile https://css-tricks.com/the-trick-to-viewport-units-on-mobile/
        // First we get the viewport height and we multiple it by 1% to get a value for a vh unit
    let vh = window.innerHeight * 0.01;
    // Then we set the value in the --vh custom property to the root of the document
    document.documentElement.style.setProperty('--vh', vh + 'px');

    /** Flash messages */
    showFlashMessages();

    /** Throttle time countdown */
    let timeSpans = document.getElementsByClassName('throttle-time-span');
    for (const timeSpan of timeSpans) {
        if (timeSpan !== null) {
            let timeInSec = parseInt(timeSpan.innerHTML);
            let timer = setInterval(function () {
                timeSpan.textContent = timeInSec;
                if (--timeInSec < 0) {
                    timeInSec = 0;
                    // document.getElementById('throttle-delay-msg').style.display = 'none';
                    // document.getElementById('form-general-error-msg').style.display = 'none';
                    timeSpan.parentElement.style.display = 'none';

                    clearInterval(timer);
                }
            }, 1000);
        }
    }
});


/**
 * Display flash messages to user
 *
 * In own function to be run client side after loading
 */
function showFlashMessages() {
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
            slideFlashOut(flash)
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