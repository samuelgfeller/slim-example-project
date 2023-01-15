import {getClientProfileCardHtml} from "./client-list-profile-card.html.js?v=0.2.0";
import {
    displayClientProfileCardLoadingPlaceholder,
    removeClientCardContentPlaceholder
} from "./client-list-content-placeholder.js?v=0.2.0";
import {fetchData} from "../../general/ajax/fetch-data.js?v=0.2.0";
import {
    disableMouseWheelClickScrolling,
    openLinkOnHtmlElement
} from "../../general/event-handler/open-link-on-html-element.js?v=0.2.0";
import {
    triggerClickOnHtmlElementEnterKeypress
} from "../../general/event-handler/trigger-click-on-enter-keypress.js?v=0.2.0";
import {submitFieldChangeWithFlash} from "../../general/ajax/submit-field-change-with-flash.js?v=0.2.0";

// When searching clients a request is made on each keyup and we want to show only the final result to the user,
// not a flickering between content placeholders, the result of the first typed key, then the second and so on.
// On each request this requestId variable increases by 1 after assigning its value to the variable previousRequest
// If previousRequestId does not match requestId it means there was newer request
let requestId = 0;
let previousRequestId = 0;

/**
 * Fetch clients with active filter chips and name search if present
 * then load clients into DOM
 * @param {URLSearchParams} filterParams custom filter params that are not from filter chip and won't be
 * saved in user_filter_setting database table
 * @param {string|null} clientWrapperId if client wrapper is not the default on the client list page,
 * a custom one can be provided.
 * @param {boolean} saveFilter if false, filter is only for fetching and should not be saved in database
 * (user filter setting). Should be true only for client list page
 */
export function fetchAndLoadClients(
    filterParams = new URLSearchParams(),
    clientWrapperId = null,
    saveFilter = false) {
    // Remove no clients text if it exists
    document.getElementById('no-clients')?.remove();

    displayClientProfileCardLoadingPlaceholder(clientWrapperId);
    fetchClients(filterParams, saveFilter).then(jsonResponse => {
        // Add one to previous request id after request is done
        previousRequestId++;
        // If previousRequestId does not match requestId it means there was newer request and this response should be ignored
        if (requestId === previousRequestId || filterParams !== new URLSearchParams()) {
            removeClientCardContentPlaceholder(clientWrapperId);
            addClientsToDom(jsonResponse.clients, jsonResponse.users, jsonResponse.statuses, clientWrapperId);
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
        }
    });

}

/**
 * Fetch and load clients into dom event handler
 * Existing because fetchAndLoadClients has an optional
 * object parameter "filterParams" so it would cause a type
 * error when called directly from event listener
 */
export function fetchAndLoadClientsEventHandler() {
    fetchAndLoadClients(new URLSearchParams(), null, true);
}


/**
 *  Fetch clients with active filter chips and name search if present
 *  then load clients into DOM
 * @return {Promise} load clients ajax promise
 * @param {URLSearchParams} searchParams custom filter params that are not from filter chip and won't be
 * saved in user_filter_setting database table
 * @param {boolean} saveFilter if false, filter is only for fetching and should not be saved in database
 * (user filter setting). Should be true only for client list page
 */
function fetchClients(searchParams = new URLSearchParams(), saveFilter = false) {

    // Loop through all the active filter chips and add filters to query params
    const activeFilterChips = document
        .querySelectorAll('#active-client-filter-chips-div .filter-chip span');
    for (const chip of activeFilterChips) {
        const paramName = chip.dataset.paramName;
        // For PHP, GET params with multiple values have to have a "[]" appended to the name
        let multiValue = '';
        // If the search param already exists
        if (searchParams.has(paramName) || searchParams.has(paramName + '[]')) {
            // [] will be added after the param name
            multiValue = '[]'
            // Param name without brackets exists, it has to be removed and re-added with brackets
            if (searchParams.has(paramName)) {
                // But the first value that didn't have the brackets,
                const firstValue = searchParams.get(paramName);
                searchParams.delete(paramName);
                searchParams.append(paramName + '[]', firstValue);
            }
        }
        // Append param to searchParams
        searchParams.append(paramName + multiValue, chip.dataset.paramValue);
        // Add filter id to filterIds param
        searchParams.append('filterIds[]', chip.dataset.filterId);
    }

    // Check if name-search-input contains values
    const searchInputValue = document.getElementById('name-search-input')?.value;
    if (searchInputValue) {
        searchParams.append('name', searchInputValue);
    }

    // Add saveFilter bool value to searchParams
    searchParams.append('saveFilter', saveFilter ? '1' : '0');

    // Add question mark
    const queryString = searchParams.toString() !== '' ? '?' + searchParams.toString() : '';
    // Add 1 to the request id
    requestId++;
    return fetchData('clients' + queryString, 'clients/list');
}

/**
 * Add client to page
 *
 * @param {object[]} clients
 * @param allUsers
 * @param allStatuses
 * @param {string|null} clientWrapperId if client wrapper is not the default on the client list page,
 * a custom one can be provided.
 */
function addClientsToDom(clients, allUsers, allStatuses, clientWrapperId = null) {
    let clientContainer = document.getElementById(clientWrapperId ?? 'client-wrapper');

    // Remove no clients text if it exists
    document.getElementById('no-clients')?.remove();
    // If no results, tell user so
    if (clients.length === 0) {
        clientContainer.insertAdjacentHTML('afterend', '<p id="no-clients">No clients found.</p>');
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
    submitFieldChangeWithFlash(this.name, this.value, `clients/${clientId}`, true, false);
}
