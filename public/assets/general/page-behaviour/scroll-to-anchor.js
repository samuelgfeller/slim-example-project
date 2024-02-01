/**
 * Scroll to url anchor (#element-id at the end of url)
 * if exists and remove anchor afterwards.
 * Source: https://stackoverflow.com/a/5298684/9013718
 */
let anchor;

export function scrollToAnchor() {
    // If anchor exists, scroll to the element
    let hash = window.location.hash.substring(1);
    hash = hash !== '' ? hash : anchor;
    if (hash) {
        document.getElementById(hash)?.scrollIntoView({
            // smooth scroll
            behavior: 'smooth',
            // the upper border of the element will be aligned at the top of the visible part of the window of the scrollable area.
            block: 'start'
        });
        // Store hash in anchor var in case there are resources that are loaded via xhr after initial page load (e.g. notes)
        anchor = hash;
        // Remove hash sign but keep history entry (to replace history entry: replaceState)
        history.pushState("", document.title, window.location.pathname + window.location.search);
    }
}