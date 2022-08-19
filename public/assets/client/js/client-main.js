import {loadClients} from "./client-loading.js";
import {basePath} from "../../general/js/config.js";

// Load clients at page startup
loadClients();

// Event delegation (event listeners on dynamically loaded elements)
document.addEventListener('click', function (e) {
    // Submit form on create button click
    if (e.target && e.target.id === 'submit-btn-create-client') {
        submitCreateClient();
    }
    // Open edit client modal after edit button click in client box
    if (e.target && e.target.className.includes('card-edit-icon')) {
        let clientId = e.target.dataset.id;
        updateClientModal(clientId);
    }
    // Submit edit client
    if (e.target && e.target.id === 'submit-btn-update-client') {
        let clientId = e.target.dataset.id;
        submitUpdateClient(clientId);
    }
    // Submit delete client
    if (e.target && e.target.className.includes('card-del-icon')) {
        let clientId = e.target.dataset.id;
        submitDeleteClient(clientId);
    }
    // Click on a card
    // https://stackoverflow.com/questions/73406779/how-to-add-event-listener-on-dynamically-created-div-with-interactive-content
    const card = e.target.closest('.client-profile-card');
    if (card && e.target.tagName !== 'SELECT') {
        window.location = basePath + 'clients/' + card.dataset.clientId;
        // console.log('redirect to ' + card.dataset.clientId);
    }
});

document.addEventListener('keydown', function (e) {
    // When user focuses the card with the keyboard (tab or arrow keys)
    const card = e.target.closest('.client-profile-card');
    // Fire click event when Enter or space bar is pressed
    if (card && (e.key === 'Enter' || e.key === ' ')) {
        card.click();
    }
});

/**
 * Show client modal loader
 */
function showClientModalLoader() {
    document.getElementById('modal-footer').insertAdjacentHTML('afterbegin',
        '<div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div>');
    let submitBtn = document.getElementsByClassName('modal-submit-btn')[0];
    submitBtn.classList.add('modal-submit-btn-loading');
    submitBtn.disabled = true;
}

/**
 * Hide client modal loader
 */
function hideClientModalLoader() {
    document.getElementsByClassName('lds-ellipsis')[0].remove();
    let submitBtn = document.getElementsByClassName('modal-submit-btn')[0];
    submitBtn.classList.remove('modal-submit-btn-loading');
    submitBtn.disabled = false;
}

