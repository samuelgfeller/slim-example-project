/**
 * Trigger click event on Enter or space bar key press
 *
 * @param {event} event
 */
export function triggerClickOnHtmlElementEnterKeypress(event) {
    // Fire click event when Enter or space bar is pressed
    if (event.key === 'Enter' || event.key === ' ') {
        this.click();
    }
}

