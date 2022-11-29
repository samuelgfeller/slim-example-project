/**
 * Make
 */
export function initCollapsible() {
    let allCollapsible = document.getElementsByClassName("collapsible-button");
    for (const collapsible of allCollapsible) {
        collapsible.addEventListener("click", function () {
            this.classList.toggle("active-collapsible");
            let content = this.nextElementSibling;
            if (content.style.maxHeight) {
                content.style.maxHeight = null;
            } else {
                content.style.maxHeight = content.scrollHeight + "px";
            }
        });
    }
}