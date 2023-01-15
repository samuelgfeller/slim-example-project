import {getClientProfileCardLoadingPlaceholderHtml} from "./client-list-profile-card.html.js?v=0.2.0";

/**
 * Display client content placeholders
 * @param {string|null} clientWrapperId if client wrapper is not the default on the client list page,
 * a custom one can be provided.
 */
export function displayClientProfileCardLoadingPlaceholder(clientWrapperId) {
    let clientWrapper = document.getElementById(clientWrapperId ?? 'client-wrapper');
    // Empty clients
    clientWrapper.innerHTML = '';

    // Add content placeholder 3 times
    for (let i = 0; i < 3; i++) {
        clientWrapper.insertAdjacentHTML('beforeend', getClientProfileCardLoadingPlaceholderHtml());
    }
}

/**
 * Remove placeholders
 * @param {string|null} clientWrapperId if client wrapper is not the default on the client list page,
 * a custom one can be provided.
 */
export function removeClientCardContentPlaceholder(clientWrapperId) {
    // Empty entire client container (and not only removing content placeholders to prevent duplicates when requests
    // are rapidly chained)
    document.getElementById(clientWrapperId ?? 'client-wrapper').innerHTML = '';
}