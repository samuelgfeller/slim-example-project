import {basePath} from "../general-js/config.js?v=0.2.0";

/**
 * Open link when click or focus and enter key press
 * on an HTML element such as a div
 *
 * @param {event} event
 * @param {Element} htmlElement
 * @param {string} route
 */
export function openLinkOnHtmlElement(event, htmlElement, route) {
    // When target is a select (or other input fields or buttons to be added on needed)
    if (htmlElement && event.target.tagName !== 'SELECT') {
        const linkToOpen = basePath + route;
        // Detect if user wants to open in new tab with mouse middle wheel button or ctrl key
        // button 1 is mouse wheel click - https://stackoverflow.com/a/49255319/9013718
        // more mouse buttons https://stackoverflow.com/a/54502280/9013718
        if (event.key === 2 || event.button === 1 || event.ctrlKey) {
            // Open link in new tab
            window.open(linkToOpen);
        } // auxclick catches other mouse click events than mouse wheel such as right click that should not open the link
        else
            // if (event.type !== 'auxclick' )
            {
            window.location = linkToOpen;
        }
    }
}

/**
 * The mouse wheel click event (auxclick and event.button === 1)
 * does not work when the page is scrollable.
 * Scroll has to be disabled first.
 * Source: https://stackoverflow.com/a/69076122/9013718
 *
 * @param event
 */
export function disableMouseWheelClickScrolling(event){
    if (event.which === 2) {
      event.preventDefault();
    }
}