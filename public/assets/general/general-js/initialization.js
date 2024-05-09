import {displayServerSideFlashMessages} from "../page-component/flash-message/flash-message.js?v=0.4.2";
import {initAutoResizingTextareaElements} from "../page-component/textarea/auto-resizing-textarea.js?v=0.4.2";
import {scrollToAnchor} from "../page-behaviour/scroll-to-anchor.js?v=0.4.2";
import {countDownThrottleTimer} from "../../authentication/throttle-timer.js?v=0.4.2";

// This file is responsible for initializing elements for every loaded page.

// displayFlashMessage('success', 'This is a success flash message.');
// displayFlashMessage('info', 'This is an info flash message.');
// displayFlashMessage('warning', 'This is a warning flash message.');
// displayFlashMessage('error', 'This is an error flash message.');

// "DOMContentLoaded" is fired when the initial HTML document has been completely loaded and parsed,
// without waiting for stylesheets, images, etc. to finish loading
window.addEventListener("DOMContentLoaded", function (event) {
    /** Auto resize textarea fields */
    initAutoResizingTextareaElements();
});

// "load" is fired when the whole page has loaded, including all dependent resources such as stylesheets, images, etc.
window.addEventListener("load", function (event) {
    /** Slide in server side flash messages */
    displayServerSideFlashMessages();

    /** Throttle time countdown */
    countDownThrottleTimer();

    /** Scroll to anchor if there is any in the url */
    scrollToAnchor();
});



