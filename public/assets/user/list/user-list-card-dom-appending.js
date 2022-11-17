import {getUserCardHtml} from "./user-list-card.html.js";

/**
 * Add elements to page
 *
 * @param {object[]} userResultDataArray
 * @param {object} statuses
 */
export function addUsersToDom(userResultDataArray, statuses) {
    let container = document.getElementById('user-wrapper');

    // If no results, tell user so
    if (userResultDataArray.length === 0) {
        container.insertAdjacentHTML('afterend', '<p>No users were found.</p>')
    }

    // Loop over users and add to DOM
    for (const userResult of userResultDataArray) {
        // Client card HTML
        let cardHtml = getUserCardHtml(userResult, statuses);

        // Add to DOM
        container.insertAdjacentHTML('beforeend', cardHtml);
    }
}