import {getClientProfileCardHtml} from "../templates/client-list-profile-card.html.js";
import {
    displayClientProfileCardLoadingPlaceholder,
    removeClientCardContentPlaceholder
} from "./client-list-loading-placeholder.js";
import {loadData} from "../../../general/js/request/load-data.js";

/**
 *  Load clients into DOM
 */
export function loadClients() {
    displayClientProfileCardLoadingPlaceholder();
    // 'own' if own clients should be loaded after creation or 'all' if all should
    let clientVisibilityScope = document.getElementById('client-wrapper').dataset.dataClientFilter;
    let queryParams = clientVisibilityScope === 'own' ? '?user=session' : '';

    loadData('clients', queryParams).then(jsonResponse => {
        removeClientCardContentPlaceholder();
        addClientsToDom(jsonResponse.clients, jsonResponse.users, jsonResponse.statuses);
    });
}

/**
 * Add client to page
 *
 * @param {object[]} clients
 * @param allUsers
 * @param allStatuses
 */
function addClientsToDom(clients, allUsers, allStatuses) {
    let clientContainer = document.getElementById('client-wrapper');

    // If no results, tell user so
    if (clients.length === 0) {
        clientContainer.insertAdjacentHTML('afterend', '<p>No clients were found.</p>')
    }


    // Loop over clients and add to DOM
    for (const client of clients) {
        // Client card HTML
        let clientProfileCardHtml = getClientProfileCardHtml(clientContainer, client.id, client.firstName, client.lastName,
            client.age, client.sex, client.location, client.phone, client.userId, client.clientStatusId, allUsers,
            allStatuses);

        // Add to DOM
        clientContainer.insertAdjacentHTML('beforeend', clientProfileCardHtml);
    }
}