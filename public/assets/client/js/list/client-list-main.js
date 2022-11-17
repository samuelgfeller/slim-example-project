import {addClientsToDom, loadClients} from "./client-list-loading.js";
import {
    disableMouseWheelClickScrolling,
    openLinkOnHtmlElement
} from "../../../general/js/eventHandler/open-link-on-html-element.js";
import {removeClientCardContentPlaceholder} from "./client-list-content-placeholder.js";
import {
    triggerClickOnHtmlElementEnterKeypress
} from "../../../general/js/eventHandler/trigger-click-on-enter-keypress.js";
import {submitFieldChangeWithFlash} from "../../../general/js/request/submit-field-change-with-flash.js";

document.addEventListener('auxclick', () => {
    console.log('okay');
});
//
// // Load clients at page startup
loadClients().then(jsonResponse => {
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
