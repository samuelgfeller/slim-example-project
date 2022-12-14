/**
 * Auto resize all given textareas
 * Source: https://stackoverflow.com/a/25621277/9013718
 * Known issue: https://stackoverflow.com/q/73475416/9013718
 */
export function initAutoResizingTextareas() {
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

    var working = false;
    function resizeTextareaNew2() {
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
        // console.log('Border bottom: '+borderBottom +'  Border top: '+borderTop+ '  padding bottom: '+paddingBottom);

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

        // Adapt height of textarea to new scrollHeight and padding
        if (scrollHeightBefore > offsetHeightBefore) {
            this.style.height = (scrollHeightBefore + adjust) + "px";
        } else {
            this.style.height = (scrollHeightAfter + adjust) + "px";
        }
        console.log(scrollHeightBefore > offsetHeightBefore, this.style.height);

        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                working = false;
            });
        });
    }
}