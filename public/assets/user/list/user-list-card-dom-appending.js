import {getUserCardHtml} from "./user-list-card.html.js?v=0.4.1";

/**
 * Add user elements to the page.
 *
 * @param {object[]} userResultDataArray
 * @param {object} statuses
 * @param {string|null} userWrapperId if user wrapper is not the default on the user list page,
 * a custom one can be provided.
 */
export function addUsersToDom(userResultDataArray, statuses, userWrapperId = null) {
    let container = document.getElementById(userWrapperId ?? 'user-wrapper');

    // Clear wrapper before adding new content
    container.innerHTML = '';

    // If no results, tell user so
    if (userResultDataArray.length === 0) {
        container.insertAdjacentHTML('beforeend', '<p>No users were found.</p>')
    }

    // Loop over users and add to DOM
    for (const userResult of userResultDataArray) {
        // Client card HTML
        let cardHtml = getUserCardHtml(userResult, statuses);

        // Add to DOM
        container.insertAdjacentHTML('beforeend', cardHtml);
    }
}