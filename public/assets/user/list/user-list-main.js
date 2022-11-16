import {basePath} from "../../general/js/config.js";
import {addUsersToDom, loadUsers} from "./user-list-card-loading.js";
import {removeUserCardContentPlaceholder} from "./user-list-card-loading-placeholder.js";

// Load clients at page startup
loadUsers().then(jsonResponse => {
    removeUserCardContentPlaceholder();
    addUsersToDom(jsonResponse.userResultDataArray, jsonResponse.statuses);
});

// Event delegation (event listeners on dynamically loaded elements)
document.addEventListener('click', initClientListEventDelegationActions);
// For mouse wheel click
document.addEventListener('auxclick', initClientListEventDelegationActions);

function initClientListEventDelegationActions(e) {

    // Click on a card
    // https://stackoverflow.com/questions/73406779/how-to-add-event-listener-on-dynamically-created-div-with-interactive-content
    const card = e.target.closest('.user-card');
    if (card && e.target.tagName !== 'SELECT') {
        const linkToOpen = basePath + 'users/' + card.dataset.clientId;
        // Detect if user wants to open in new tab with mouse middle wheel button or ctrl key
        if (e.key === 2 || e.button === 1 || e.ctrlKey) {
            // Open link in new tab
            window.open(linkToOpen);
        } else {
            window.location = linkToOpen;
        }
        // console.log('redirect to ' + card.dataset.clientId);
    }
}