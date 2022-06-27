import {loadClients} from "./client-loading";

/**
 * Create and display modal box to create a new client
 */
function createClientModal() {
    let header = '<h2>Client</h2>';
    let body = '<div class="form modal-form">' + '<textarea rows="4" cols="50" name="message" ' +
        'id="create-message-textarea" class="form-input" ' + 'placeholder="Your message here." minlength="4" ' +
        'maxlength="500" required></textarea>' + '</div>';
    let footer = '<button type="button" id="submit-btn-create-client" class="submit-btn modal-submit-btn">' +
        'Create client</button>' + '<div class="clearfix"></div>' + '</div>';
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
