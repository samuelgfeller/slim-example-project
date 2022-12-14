/**
 * Remove max-height on elements with class name collapsible-button on click
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
        // Remove event listener does not work here so initCollapsible cannot be called by default in default.js as
        // event is registered multiple times otherwise
        // collapsible.removeEventListener("click", handler);
        collapsible.addEventListener("click", handler);
    }
}