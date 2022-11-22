import {getClientProfileCardHtml} from "./client-list-profile-card.html.js?v=0.1";
import {
    displayClientProfileCardLoadingPlaceholder,
    removeClientCardContentPlaceholder
} from "./client-list-content-placeholder.js?v=0.1";
import {fetchData} from "../../general/js/request/fetch-data.js?v=0.1";
import {
    disableMouseWheelClickScrolling,
    openLinkOnHtmlElement
} from "../../general/js/eventHandler/open-link-on-html-element.js?v=0.1";
import {
    triggerClickOnHtmlElementEnterKeypress
} from "../../general/js/eventHandler/trigger-click-on-enter-keypress.js?v=0.1";
import {submitFieldChangeWithFlash} from "../../general/js/request/submit-field-change-with-flash.js?v=0.1";


export function fetchAndLoadClients(){
    fetchClients().then(jsonResponse => {
        removeClientCardContentPlaceholder();
        addClientsToDom(jsonResponse.clients, jsonResponse.users, jsonResponse.statuses);
        // Add event listeners to cards
        let cards = document.querySelectorAll('.client-profile-card');
        for (const card of cards) {
            // Click on user card
            card.addEventListener('click', openClientReadPageOnCardClick);
            // Middle mouse wheel click
            card.addEventListener('auxclick', openClientReadPageOnCardClick);
            card.addEventListener('mousedown', disableMouseWheelClickScrolling);
            // Enter or space bar key press
            card.addEventListener('keypress', triggerClickOnHtmlElementEnterKeypress);

            // Status select change
            // "this" context only passed to event handling function if it's not an anonymous
            card.querySelector('select[name="client_status_id"]:not([disabled])')
                ?.addEventListener('change', submitClientCardDropdownChange);
            // User role select change
            card.querySelector('select[name="user_id"]:not([disabled])')
                ?.addEventListener('change', submitClientCardDropdownChange);
        }
    });
}

/**
 *  Load clients into DOM
 *  @return {Promise} load clients ajax promise
 */
function fetchClients() {
    displayClientProfileCardLoadingPlaceholder();
    // 'own' if own clients should be loaded after creation or 'all' if all should
    let clientVisibilityScope = document.getElementById('client-wrapper').dataset.dataClientFilter;
    let queryParams = clientVisibilityScope === 'own' ? '?user=session' : '';

    return fetchData('clients' + queryParams, 'clients/list');
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
        let clientProfileCardHtml = getClientProfileCardHtml(client, allUsers, allStatuses);
        // // Add to DOM
        clientContainer.insertAdjacentHTML('beforeend', clientProfileCardHtml);
    }
}

/**
 * Click on user card event handler
 * @param event
 */
function openClientReadPageOnCardClick(event) {
    // "this" is the card
    openLinkOnHtmlElement(event, this, `clients/${this.dataset.clientId}`);
}

/**
 * User card select change event handler
 */
function submitClientCardDropdownChange() {
    // "this" is the select element
    // Search upwards the closest user-card that contains the data-user-id attribute
    let clientId = this.closest('.client-profile-card').dataset.clientId;

    // Submit field change with flash message indicating that change was successful
    submitFieldChangeWithFlash(this.name, this.value, `clients/${clientId}`);
}
