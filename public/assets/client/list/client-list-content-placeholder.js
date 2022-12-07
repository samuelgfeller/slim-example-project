import {getClientProfileCardLoadingPlaceholderHtml} from "./client-list-profile-card.html.js?v=0.1";

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
    // Empty entire client container (and not only removing content placeholders to prevent duplicates when requests
    // are rapidly chained)
    document.getElementById('client-wrapper').innerHTML = '';
}