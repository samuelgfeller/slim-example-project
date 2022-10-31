import {loadClients} from "../list/client-list-loading.js";

/**
 * Create and display modal box to create a new client
 */
export function displayClientCreateModal() {
    let header = '<h2>Create client</h2>';
    let body = `<div class="modal-form wide-modal-form">
        <div class="wide-modal-form-input-group">
            <label>First name</label>
            <input type="text" name="first_name" placeholder="Hans" class="form-input">
        </div>
        <div class="wide-modal-form-input-group">
            <label>Last name</label>
            <input type="text" name="last_name" placeholder="Zimmer" class="form-input">
        </div>
        <div class="wide-modal-form-input-group">
            <label>Birthdate</label>
            <input type="date" name="birthdate" placeholder="15.03.2000" class="form-input">
        </div>
        <div class="wide-modal-form-input-group">
            <label>Location</label>
            <input type="text" placeholder="Basel" class="form-input">
        </div>
        <div class="wide-modal-form-input-group double-width-modal-form-input-group">
            <label for="create-message-textarea" class="form-label">Main note</label>
            <textarea rows="4" cols="50" name="message" id="create-message-textarea" class="form-input"
                      placeholder="Your message here." minlength="4" maxlength="500" required></textarea>
        </div>
        <div class="wide-modal-form-input-group" id="client-sex-input-group-div">
            <label>Sex</label><br>
            <!-- Sex radio buttons are added after modal load   -->
        </div>
        <div class="wide-modal-form-input-group">
            <label>Phone number</label>
            <input type="text" name="phone" placeholder="061 422 32 11" class="form-input">
        </div>
        <div class="wide-modal-form-input-group">
            <label>E-Mail</label>
            <input type="text" name="email" placeholder="mail@example.com" class="form-input">
        </div>
        <div class="wide-modal-form-input-group">
            <label>Assigned user</label>
            <select name="user_id" class="form-select" id="assigned-user-select">
                <!-- Dropdown options loaded afterwards -->
            </select>
        </div>
        <div class="wide-modal-form-input-group">
            <label>Status</label>
            <select name="client_status_id" id="client-status-select" class="form-select">
            <!-- Dropdown options loaded afterwards -->
            </select>
        </div>
    </div>`;
    let footer = `<button type="button" id="submit-btn-create-client" class="submit-btn modal-submit-btn">Create client
    </button>
    <div class="clearfix">
    </div>`;
    document.getElementById('client-wrapper').insertAdjacentHTML('afterend', '<div id="create-client-div"></div>');
    let container = document.getElementById('create-client-div');
    createModal(header, body, footer, container);
}

/**
 * Send client creation to server
 *
 * @param formId
 */
function submitCreateClient(formId) {
    // Check if textarea content is valid (frontend validation)
    let textArea = document.getElementById('create-message-textarea')
    if (textArea.checkValidity() === false) {
        // If not valid, report to user and return void
        textArea.reportValidity();
        return;
    }

    // Show loader to indicate user that the request is on its way
    showClientModalLoader();

    let xHttp = new XMLHttpRequest();
    xHttp.onreadystatechange = function () {
        if (xHttp.readyState === XMLHttpRequest.DONE) {
            // Fail
            if (xHttp.status !== 201 && xHttp.status !== 200) {
                // Default fail handler
                handleFail(xHttp);
            }
            // Success
            else {
                closeModal();
                loadClients();

                // Hide loader
                hideClientModalLoader();
            }
        }
    };

    xHttp.open('POST', basePath + 'clients', true);
    xHttp.setRequestHeader("Content-type", "application/json");

    // Data format: "fname=Henry&lname=Ford"
    // In [square brackets] to be evaluated
    xHttp.send(JSON.stringify({[textArea.name]: textArea.value}));
}
