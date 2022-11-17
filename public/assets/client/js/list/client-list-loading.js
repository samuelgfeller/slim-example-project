import {getClientProfileCardHtml} from "../templates/client-list-profile-card.html.js";
import {displayClientProfileCardLoadingPlaceholder} from "./client-list-content-placeholder.js";
import {loadData} from "../../../general/js/request/load-data.js";

/**
 *  Load clients into DOM
 *  @return {Promise} load clients ajax promise
 */
export function loadClients() {
    displayClientProfileCardLoadingPlaceholder();
    // 'own' if own clients should be loaded after creation or 'all' if all should
    let clientVisibilityScope = document.getElementById('client-wrapper').dataset.dataClientFilter;
    let queryParams = clientVisibilityScope === 'own' ? '?user=session' : '';

    return loadData('clients', queryParams);
}

/**
 * Add client to page
 *
 * @param {object[]} clients
 * @param allUsers
 * @param allStatuses
 */
export function addClientsToDom(clients, allUsers, allStatuses) {
    let clientContainer = document.getElementById('client-wrapper');

    // If no results, tell user so
    if (clients.length === 0) {
        clientContainer.insertAdjacentHTML('afterend', '<p>No clients were found.</p>')
    }

    // Loop over clients and add to DOM
    for (const client of clients) {
        // Client card HTML
        let clientProfileCardHtml = getClientProfileCardHtml(client, allUsers, allStatuses);
        // // Add to DOM
        clientContainer.insertAdjacentHTML('beforeend', clientProfileCardHtml);
    }
}