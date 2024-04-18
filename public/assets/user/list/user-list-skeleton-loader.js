import {getUserCardLoadingPlaceholderHtml} from "./user-list-card.html.js?v=0.4.0";

/**
 * Display content placeholders
 * @param {string|null} userWrapperId if client wrapper is not the default on the client list page,
 * a custom one can be provided.
 */
export function displayUserCardSkeletonLoader(userWrapperId = null) {
    let wrapper = document.getElementById(userWrapperId ?? 'user-wrapper');
    // Empty clients
    wrapper.innerHTML = '';

    // Add content placeholder 3 times
    for (let i = 0; i < 3; i++) {
        wrapper.insertAdjacentHTML('beforeend', getUserCardLoadingPlaceholderHtml());
    }
}

/**
 * Remove placeholders
 */
export function removeUserCardSkeletonLoader() {
    let skeletonLoaders = document.querySelectorAll('.user-card-skeleton-loader');
    // Foreach loop over content placeholders
    for (let skeletonLoader of skeletonLoaders) {
        // remove from DOM
        skeletonLoader.remove();
    }
}