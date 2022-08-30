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

// DOMContentLoaded faster than load as it doesn't wait on all resources
window.addEventListener("DOMContentLoaded", function (event) {
    /** Auto resize textareas */
    initAutoResizingTextareas();
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

/**
 * Auto resize all given textareas
 * Source: https://stackoverflow.com/a/25621277/9013718
 * Known issue: https://stackoverflow.com/q/73475416/9013718
 */
function initAutoResizingTextareas() {
    // Target all textarea fields that have class name auto-resize-textarea
    let textareas = document.getElementsByClassName("auto-resize-textarea");
    // Init observer to call resizeTextarea when the dimensions of the textareas change
    let observer = new ResizeObserver(entries => {
        for (let entry of entries) {
            // Call function to resize textarea with textarea element as "this" context
            resizeTextarea.call(entry.target);
        }
    });
    // Loop through textareas and add event listeners as well as other needed css attributes
    for (const textarea of textareas) {
        // Initially set height as otherwise the textarea is not high enough on load
        textarea.style.height = textarea.scrollHeight.toString();
        // Hide scrollbar
        textarea.style.overflowY = 'hidden';
        // Call resize function with "this" context once during initialisation as it's too high otherwise
        resizeTextarea.call(textarea);
        // Add event listener to resize textarea on input
        textarea.addEventListener('input', resizeTextarea, false);
        // Also resize textarea on window resize event binding textarea to be "this"
        // window.addEventListener('resize', resizeTextarea.bind(textarea), false);
        // Observe all text area resize change events (also works when scrollbar appears)
        observer.observe(textarea);
    }

    var working = false;
    function resizeTextareaNew() {
        if (working) {
            console.log('working');
            return;
        }
        working = true;

        // Textareas have default 2px padding and if not set it returns 0px
        const style = getComputedStyle(this);
        const paddingBottom = Number.parseFloat(style.getPropertyValue('padding-bottom'));
        const paddingTop = Number.parseFloat(style.getPropertyValue('padding-top'));
        const borderBottom = Number.parseFloat(style.getPropertyValue('border-bottom'));
        const borderTop = Number.parseFloat(style.getPropertyValue('border-top'));
        const adjust = borderBottom + borderTop;

        // Reset textarea height to (supposedly) have correct scrollHeight afterwards
        const scrollHeightBefore = this.scrollHeight;
        const offsetHeightBefore = this.offsetHeight;
        const styleHeightBefore = this.style.height;
        console.log('before', scrollHeightBefore, offsetHeightBefore, styleHeightBefore);
        this.style.height = "0px";

        const scrollHeightAfter = this.scrollHeight;
        const offsetHeightAfter = this.offsetHeight;
        const styleHeightAfter = this.style.height;
        console.log('after', scrollHeightAfter, offsetHeightAfter, styleHeightAfter);

        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                working = false;
            });
            // Adapt height of textarea to new scrollHeight and padding
            if (scrollHeightBefore > offsetHeightBefore) {
                this.style.height = (scrollHeightBefore + adjust) + "px";
            } else {
                this.style.height = (scrollHeightAfter + adjust) + "px";
            }
            console.log(scrollHeightBefore > offsetHeightBefore, this.style.height);
        });
    }
    function resizeTextarea() {
        // Textareas have default 2px padding and if not set it returns 0px
        let padding = window.getComputedStyle(this).getPropertyValue('padding-bottom');
        // getPropertyValue('padding-bottom') returns "px" at the end it needs to be removed to be added to scrollHeight
        padding = parseInt(padding.replace('px', ''));
        // console.log('scrollHeight before height reset: '+this.scrollHeight);
        // Reset textarea height to (supposedly) have correct scrollHeight afterwards
        this.style.height = "1px";
        // console.log('scrollHeight after height reset: ' + this.scrollHeight);
        // Adapt height of textarea to new scrollHeight and padding
        this.style.height = (this.scrollHeight + padding) + "px";
    }
}