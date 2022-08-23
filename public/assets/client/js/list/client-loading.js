import {getClientProfileCardHtml} from "../templates/client-profile-card.html.js";
import {displayClientProfileCardLoadingPlaceholder, removeContentPlaceholder} from "./client-loading-placeholder.js";
import {basePath} from "../../../general/js/config.js";

/**
 *  Load clients into DOM
 */
export function loadClients() {
    displayClientProfileCardLoadingPlaceholder();
    // 'own' if own clients should be loaded after creation or 'all' if all should
    let clientVisibilityScope = document.getElementById('client-wrapper').dataset.dataClientFilter;
    let queryParams = clientVisibilityScope === 'own' ? '?user=session' : '';

    let xHttp = new XMLHttpRequest();
    xHttp.onreadystatechange = function () {
        if (xHttp.readyState === XMLHttpRequest.DONE) {
            // Fail
            if (xHttp.status !== 200) {
                // Default fail handler
                handleFail(xHttp);
            }
            // If status code 401 user is not logged in
            if (xHttp.status === 401) {
                removeContentPlaceholder();
                document.getElementById('client-wrapper').insertAdjacentHTML('afterend',
                    '<p>Please <a href="' + JSON.parse(xHttp.responseText).loginUrl +
                    '">login</a> to access clients assigned to you.</p>');
            }
            // Success
            else {
                let response = JSON.parse(xHttp.responseText);
                removeContentPlaceholder();
                console.log(response.clients);
                addClientsToDom(response.clients, response.users, response.statuses);
            }
        }
    };

    // For GET requests, query params have to be passed in the url directly. They are ignored in send()
    xHttp.open('GET', basePath + 'clients' + queryParams, true);
    xHttp.setRequestHeader("Content-type", "application/json");

    xHttp.send();
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
        let clientProfileCardHtml = getClientProfileCardHtml(clientContainer, client.id, client.first_name, client.last_name,
            client.age, client.sex, client.location, client.phone, client.user_id, client.client_status_id, allUsers,
            allStatuses);

        // Add to DOM
        clientContainer.insertAdjacentHTML('beforeend', clientProfileCardHtml);
    }
}