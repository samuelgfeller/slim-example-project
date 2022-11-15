import {submitCreateClient} from "./client-create-request.js";
import {displayClientCreateModal} from "../templates/client-create-modal.html.js";

// Init event listeners
document.getElementById('create-client-btn').addEventListener('click', displayClientCreateModal);

// Submit form on create button click
document.addEventListener('click', e => {
    // Event delegation as modal is removed and added dynamically
    if (e.target && e.target.id === 'client-create-submit-btn') {
        submitCreateClient();
    }
});
