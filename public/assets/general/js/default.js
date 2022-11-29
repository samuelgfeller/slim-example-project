import {displayServerSideFlashMessages} from "./requestUtil/flash-message.js?v=0.1";
import {initAutoResizingTextareas} from "./pageComponents/auto-resizing-textarea.js?v=0.1";
import {initCollapsible} from "./pageComponents/collapsible.js?v=0.1";
import {scrollToAnchor} from "./page/scroll-to-anchor.js?v=0.1";
import {countDownThrottleTimer} from "../../authentication/throttle-timer.js?v=0.1";

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

    /** Init collapsible */
    initCollapsible();

    /** Scroll to anchor if there are some in url */
    scrollToAnchor();
});



