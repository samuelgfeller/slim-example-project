import {getClientProfileCardSkeletonLoaderHtml} from "./client-list-profile-card.html.js?v=1.0.0";

/**
 * Display client skeleton loaders
 * @param {string|null} clientWrapperId if client wrapper is not the default on the client list page,
 * a custom one can be provided.
 */
export function displayClientProfileCardSkeletonLoader(clientWrapperId) {
    let clientWrapper = document.getElementById(clientWrapperId ?? 'client-list-wrapper');
    // Empty clients
    clientWrapper.innerHTML = '';

    // Add content placeholder 3 times
    for (let i = 0; i < 3; i++) {
        clientWrapper.insertAdjacentHTML('beforeend', getClientProfileCardSkeletonLoaderHtml());
    }
}

/**
 * Remove placeholders
 * @param {string|null} clientWrapperId if client wrapper is not the default on the client list page,
 * a custom one can be provided.
 */
export function removeClientCardSkeletonLoader(clientWrapperId) {
    // Empty entire client container (and not only removing skeleton loaders to prevent duplicates when requests
    // are rapidly chained)
    document.getElementById(clientWrapperId ?? 'client-list-wrapper').innerHTML = '';
}