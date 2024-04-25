/**
 * Remove max-height on elements with class name collapsible-button on click
 *
 * This function should NOT be called in initialization.js for every page load; only when needed.
 */
export function initCollapsible() {
    let allCollapsible = document.getElementsByClassName("collapsible-button");
    // console.trace()
    for (const collapsible of allCollapsible) {
        let handler = () => {
            collapsible.classList.toggle("open-collapsible");
            let content = collapsible.nextElementSibling;
            if (content.style.maxHeight) {
                content.style.maxHeight = null;
            } else {
                content.style.maxHeight = content.scrollHeight + "px";
            }
        };
        // Remove event listener does not work here, so initCollapsible cannot be called by
        // default in initialization.js as event is registered multiple times otherwise
        collapsible.addEventListener("click", handler);
    }
}