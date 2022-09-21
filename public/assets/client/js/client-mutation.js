import {loadClients} from "./list/client-list-loading.js";

/**
 * After the click on the edit icon of a client, a modal box is opened
 * with an editable textarea containing the most recent content (request to server)
 *
 * @param {string} clientId
 */
function updateClientModal(clientId) {

    let header = '<h2>Edit client</h2>';
    let body = '<div class="form modal-form"><textarea rows="4" cols="50" name="message" ' +
        'id="update-message-textarea" class="form-input" minlength="4" ' +
        'maxlength="500" required disabled>Loading...</textarea></div>';
    let footer = '<button type="button" disabled id="submit-btn-update-client" class="submit-btn modal-submit-btn">' +
        'Update client</button><div class="clearfix"></div>';

    document.getElementById('client-wrapper').insertAdjacentHTML(
        'afterend', '<div id="update-client-modal"></div>');
    let container = document.getElementById('update-client-modal');

    createModal(header, body, footer, container);

    // Retrieve actual client infos via Ajax and populate textarea
    let xHttp = new XMLHttpRequest();
    xHttp.onreadystatechange = function () {
        if (xHttp.readyState === XMLHttpRequest.DONE) {
            // Fail
            if (xHttp.status !== 200) {
                // Default fail handler
                handleFail(xHttp);
            }
            // Success
            else {
                let output = JSON.parse(xHttp.responseText);
                let updateMessageTextarea = document.getElementById('update-message-textarea');
                updateMessageTextarea.value = output.message;
                updateMessageTextarea.disabled = false;
                let submitClientUpdateBtn = document.getElementById('submit-btn-update-client');
                submitClientUpdateBtn.disabled = false;
                // Set client id on submit button as its easiest to retrieve on delegated event listener
                submitClientUpdateBtn.setAttribute('data-id', output.id);
            }
        }
    };

    // Read client infos
    xHttp.open('GET', basePath + 'clients/' + clientId, true);
    xHttp.setRequestHeader("Content-type", "application/json");
    xHttp.send();
}

/**
 * Submit client change
 *
 * @param {string} clientId
 */
function submitUpdateClient(clientId) {
    let updateMessageTextarea = document.getElementById('update-message-textarea');

    // Show loader to indicate user that the request is on its way
    showClientModalLoader();

    // Ajax request
    let xHttp = new XMLHttpRequest();
    xHttp.onreadystatechange = function () {
        if (xHttp.readyState === XMLHttpRequest.DONE) {
            // Fail
            if (xHttp.status !== 200) {
                // Default fail handler
                handleFail(xHttp);
            }
            // Success
            else {
                hideClientModalLoader();
                closeModal();
                loadClients();
            }
        }
    };

    // Read client infos
    xHttp.open('PUT', basePath + 'clients/' + clientId, true);
    xHttp.setRequestHeader("Content-type", "application/json");
    // In square brackets to be evaluated
    xHttp.send(JSON.stringify({[updateMessageTextarea.name]: updateMessageTextarea.value}));
}