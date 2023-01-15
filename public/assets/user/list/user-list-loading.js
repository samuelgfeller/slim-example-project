import {
    displayUserCardLoadingPlaceholder,
    removeUserCardContentPlaceholder
} from "./user-list-content-placeholder.js?v=0.2.0";
import {fetchData} from "../../general/ajax/fetch-data.js?v=0.2.0";
import {addUsersToDom} from "./user-list-card-dom-appending.js?v=0.2.0";
import {
    disableMouseWheelClickScrolling,
    openLinkOnHtmlElement
} from "../../general/event-handler/open-link-on-html-element.js?v=0.2.0";
import {
    triggerClickOnHtmlElementEnterKeypress
} from "../../general/event-handler/trigger-click-on-enter-keypress.js?v=0.2.0";
import {submitFieldChangeWithFlash} from "../../general/ajax/submit-field-change-with-flash.js?v=0.2.0";

/**
 * Load user list into DOM
 *
 * @param {string|null} userWrapperId if client wrapper is not the default on the client list page,
 * a custom one can be provided.
 */
export function loadUserList(userWrapperId = null) {
    // Display content placeholder
    displayUserCardLoadingPlaceholder(userWrapperId);
    // Fetch users
    fetchData('users', 'users/list').then(jsonResponse => {
        removeUserCardContentPlaceholder();
        addUsersToDom(jsonResponse.userResultDataArray, jsonResponse.statuses, userWrapperId);
        // Add event listeners to cards
        let userCards = document.querySelectorAll('.user-card');
        for (const card of userCards) {
            // Click on user card
            card.addEventListener('click', openUserReadPageOnCardClick);
            // Middle mouse wheel click
            card.addEventListener('auxclick', openUserReadPageOnCardClick);
            card.addEventListener('mousedown', disableMouseWheelClickScrolling);
            // Enter or space bar key press
            card.addEventListener('keypress', triggerClickOnHtmlElementEnterKeypress);

            // Status select change
            // "this" context only passed to event handling function if it's not an anonymous
            card.querySelector('select[name="status"]:not([disabled])')
                ?.addEventListener('change', submitUserCardDropdownChange);
            // User role select change
            card.querySelector('select[name="user_role_id"]:not([disabled])')
                ?.addEventListener('change', submitUserCardDropdownChange);
        }
    });
}

/**
 * Click on user card event handler
 *
 * @param event
 */
function openUserReadPageOnCardClick(event) {
    // "this" is the card
    openLinkOnHtmlElement(event, this, `users/${this.dataset.userId}`);
}

/**
 * User select change event handler
 */
function submitUserCardDropdownChange() {
    // "this" is the select element
    // Search upwards the closest user-card that contains the data-user-id attribute
    let userId = this.closest('.user-card').dataset.userId;

    submitFieldChangeWithFlash(this.name, this.value, `users/${userId}`, true, false);
}