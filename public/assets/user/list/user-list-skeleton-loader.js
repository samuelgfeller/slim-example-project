import {getUserCardSkeletonLoaderHtml} from "./user-list-card.html.js?v=0.4.1";

/**
 * Display skeleton loaders
 *
 * @param {string|null} userWrapperId if user wrapper is not the default on the user list page,
 * a custom one can be provided.
 */
export function displayUserCardSkeletonLoader(userWrapperId = null) {
    let wrapper = document.getElementById(userWrapperId ?? 'user-wrapper');
    // Empty users
    wrapper.innerHTML = '';

    // Add content placeholder 3 times
    for (let i = 0; i < 3; i++) {
        wrapper.insertAdjacentHTML('beforeend', getUserCardSkeletonLoaderHtml());
    }
}

/**
 * Remove placeholders
 */
export function removeUserCardSkeletonLoader() {
    let skeletonLoaders = document.querySelectorAll('.user-card-skeleton-loader');
    // Foreach loop over skeleton loaders
    for (let skeletonLoader of skeletonLoaders) {
        // remove from DOM
        skeletonLoader.remove();
    }
}