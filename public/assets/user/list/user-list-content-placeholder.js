import {getUserCardLoadingPlaceholderHtml} from "./user-list-card.html.js?v=0.1";

/**
 * Display content placeholders
 */
export function displayUserCardLoadingPlaceholder() {
    let wrapper = document.getElementById('user-wrapper');
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
export function removeUserCardContentPlaceholder() {
    // I had a very strange issue. With getElementsByClassName I got 3 elements but only 2 seem to be looped through
    let contentPlaceholders = document.querySelectorAll('.user-card-loading-placeholder');
    // Foreach loop over content placeholders
    for (let contentPlaceholder of contentPlaceholders) {
        // remove from DOM
        contentPlaceholder.remove();
    }
}