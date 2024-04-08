/**
 * Auto resize all given textarea elements with class name auto-resize-textarea
 * Source: https://stackoverflow.com/a/25621277/9013718
 * Known issue when window is resized and scrollbar appears, depending on the width of the scrollbar
 * and the text content it may not expand the textarea enough: https://stackoverflow.com/q/73475416/9013718
 */
export function initAutoResizingTextareaElements() {
    // Target all textarea fields that have the class name auto-resize-textarea
    let textareaElements = document.getElementsByClassName("auto-resize-textarea");
    // Init observer to call resizeTextarea when the dimensions of the textareaElements change
    let observer = new ResizeObserver(entries => {
        for (let entry of entries) {
            // Call function to resize textarea with textarea element as "this" context
            resizeTextarea.call(entry.target);
        }
    });
    // Loop through textareaElements and add event listeners as well as other needed css attributes
    for (const textarea of textareaElements) {
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

    function resizeTextarea() {
        // Textarea elements have default 2px padding and if not set it returns 0px
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