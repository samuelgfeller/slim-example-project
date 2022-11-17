import {loadClients} from "./client-list-loading.js";
import {basePath} from "../../../general/js/config.js";

// Load clients at page startup
loadClients();

// Event delegation (event listeners on dynamically loaded elements)
document.addEventListener('click', initClientListEventDelegationActions);
// For mouse wheel click
document.addEventListener('auxclick', initClientListEventDelegationActions);

function initClientListEventDelegationActions(e) {

    // Click on a card
    // https://stackoverflow.com/questions/73406779/how-to-add-event-listener-on-dynamically-created-div-with-interactive-content
    const card = e.target.closest('.client-profile-card');
    if (card && e.target.tagName !== 'SELECT') {
        const linkToOpenClient = basePath + 'clients/' + card.dataset.clientId;
        // Detect if user wants to open in new tab with mouse middle wheel button or ctrl key
        if (e.key === 2 || e.button === 1 || e.ctrlKey) {
            // Open link in new tab
            window.open(linkToOpenClient);
        } else {
            window.location = linkToOpenClient;
        }
    }
    // // Submit delete client
    // if (e.target && e.target.className.includes('card-del-icon')) {
    //     let clientId = e.target.dataset.id;
    //     submitDeleteClient(clientId);
    // }
}

document.addEventListener('keydown', function (e) {
    // When user focuses the card with the keyboard (tab or arrow keys)
    const card = e.target.closest('.client-profile-card');
    // Fire click event when Enter or space bar is pressed
    if (card && (e.key === 'Enter' || e.key === ' ')) {
        card.click();
    }
});