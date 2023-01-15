import {displayServerSideFlashMessages} from "../page-component/flash-message/flash-message.js?v=0.2.0";
import {initAutoResizingTextareas} from "../page-component/textarea/auto-resizing-textarea.js?v=0.2.0";
import {scrollToAnchor} from "../page-behaviour/scroll-to-anchor.js?v=0.2.0";
import {countDownThrottleTimer} from "../../authentication/throttle-timer.js?v=0.2.0";

// displayFlashMessage('success', 'This is a success flash message.');
// displayFlashMessage('info', 'This is an info flash message.');
// displayFlashMessage('warning', 'This is a warning flash message.');
// displayFlashMessage('error', 'This is an error flash message.');
// DOMContentLoaded faster than load as it doesn't wait on all resources

window.addEventListener("DOMContentLoaded", function (event) {
    /** Auto resize textareas */
    initAutoResizingTextareas();
});

window.addEventListener("load", function (event) {
    /** Remove scrollbar on mobile - mobile*/
    // vh without scrollbar on mobile https://css-tricks.com/the-trick-to-viewport-units-on-mobile/
    // First we get the viewport height, and we multiply it by 1% to get a value for a vh unit
    let vh = window.innerHeight * 0.01;
    // Then we set the value in the --vh custom property to the root of the document
    document.documentElement.style.setProperty('--vh', vh + 'px');

    /** Slide in server side flash messages */
    displayServerSideFlashMessages();

    /** Throttle time countdown */
    countDownThrottleTimer();

    /** Init collapsible should NOT be called in default; only when used as it breaks
     * user-read activity panel collapsible as event listeners are registered twice for some reason
     * even if I make a non-anonymous event handler and remove the event listener first
     * Note: that happens only if initCollapsible() is called the first time from default.js and not if
     * another place called initCollapsible before*/
    // initCollapsible();

    /** Scroll to anchor if there are some in url */
    scrollToAnchor();
});



