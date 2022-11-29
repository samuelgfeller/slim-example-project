/**
 * Scroll to url anchor (#element-id at the end of url)
 * if exists and remove anchor afterwards.
 * Source: https://stackoverflow.com/a/5298684/9013718
 */
export function scrollToAnchor(){
    // If anchor exists, scroll to the element
    let hash = window.location.hash.substring(1);
    if (hash) {
        document.getElementById(hash)?.scrollIntoView({
          behavior: 'smooth', // smooth scroll
          block: 'start' // the upper border of the element will be aligned at the top of the visible part of the window of the scrollable area.
        });
        // Remove hash sign but keep history entry (to replace history entry: replaceState)
        history.pushState("", document.title, window.location.pathname + window.location.search);
    }
}