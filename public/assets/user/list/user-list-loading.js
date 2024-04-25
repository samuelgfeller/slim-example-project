import {displayUserCardSkeletonLoader, removeUserCardSkeletonLoader} from "./user-list-skeleton-loader.js?v=0.4.0";
import {fetchData} from "../../general/ajax/fetch-data.js?v=0.4.0";
import {addUsersToDom} from "./user-list-card-dom-appending.js?v=0.4.0";
import {
    disableMouseWheelClickScrolling,
    openLinkOnHtmlElement
} from "../../general/event-handler/open-link-on-html-element.js?v=0.4.0";
import {
    triggerClickOnHtmlElementEnterKeypress
} from "../../general/event-handler/trigger-click-on-enter-keypress.js?v=0.4.0";
import {submitUpdate} from "../../general/ajax/submit-update-data.js?v=0.4.0";

/**
 * Load user list into DOM
 *
 * @param {string|null} userWrapperId if user wrapper is not the default on the user list page,
 * a custom one can be provided.
 */
export function loadUserList(userWrapperId = null) {
    // Display content placeholder
    displayUserCardSkeletonLoader(userWrapperId);
    // Fetch users
    fetchData('users').then(jsonResponse => {
        removeUserCardSkeletonLoader();
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
    }).catch(exception => {
        console.error(exception);
        removeUserCardSkeletonLoader();
    });
}

/**
 * Click on user card event handler
 *
 * @param event
 */
function openUserReadPageOnCardClick(event) {
    // Don't open client read if not left-click, or middle mouse wheel, or select option click
    if ((event.type === 'auxclick' && event.button !== 1) || event.target.tagName === 'OPTION') {
        return;
    }
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

    submitUpdate({[this.name]: this.value}, `users/${userId}`)
        .then(r => {
        });
}