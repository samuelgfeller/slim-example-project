import {getClientProfileCardLoadingPlaceholderHtml} from "../templates/client-list-profile-card.html.js";

/**
 * Display client content placeholders
 */
export function displayClientProfileCardLoadingPlaceholder() {
    let clientWrapper = document.getElementById('client-wrapper');
    // Empty clients
    clientWrapper.innerHTML = '';

    // Add content placeholder 3 times
    for (let i = 0; i < 3; i++) {
        clientWrapper.insertAdjacentHTML('beforeend', getClientProfileCardLoadingPlaceholderHtml());
    }
}

/**
 * Remove placeholders
 */
export function removeClientCardContentPlaceholder() {
    // I had a very strange issue. With getElementsByClassName I got 3 elements but only 2 seem to be looped through
    let contentPlaceholders = document.querySelectorAll('.client-profile-card-loading-placeholder');
    // Foreach loop over content placeholders
    for (let contentPlaceholder of contentPlaceholders) {
        // remove from DOM
        contentPlaceholder.remove();
    }
}