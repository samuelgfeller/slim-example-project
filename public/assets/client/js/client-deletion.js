import {loadClients} from "./list/client-loading.js";

/**
 * Submit client deletion
 *
 * @param {string} clientId
 */
function submitDeleteClient(clientId) {

    // Replace delete icon with loader to indicate user that the request is on its way
    showClientDeleteLoader(clientId);
    return;
    // Ajax request
    let xHttp = new XMLHttpRequest();
    xHttp.onreadystatechange = function () {
        if (xHttp.readyState === XMLHttpRequest.DONE) {
            // Not logged in, redirect to login url
            if (xHttp.status === 401) {
                window.location.href = JSON.parse(xHttp.responseText).loginUrl;
            }
            // Fail
            if (xHttp.status !== 200) {
                // Default fail handler
                handleFail(xHttp);
                // Reload clients on fail as issue may be resolved with refresh
                loadClients();
            }
            // Success
            else {
                // Remove client card after successful deletion
                document.getElementById('client' + clientId).remove();
            }

            // After request is done, reset loader size no matter if success or failure
            document.documentElement.style.setProperty('--three-dots-loader-factor', '0.65');
        }
    };

    // Read client infos
    xHttp.open('DELETE', basePath + 'clients/' + clientId, true);
    // Important to add content type json and "Redirect-to-if-unauthorized" header for the UserAuthenticationMiddleware
    // to know to send the login url in the json response body and where to redirect back after a successful login
    xHttp.setRequestHeader("Content-type", "application/json");
    xHttp.setRequestHeader("Redirect-to-if-unauthorized", "client-list-assigned-to-me-page");

    xHttp.send();
}

/**
 * Replace delete icon with loader to indicate user that the request is on its way
 */
function showClientDeleteLoader(clientId) {
    // Insert loader
    // document.querySelector('.card-edit-icon[data-id="' + clientId + '"]').insertAdjacentHTML('afterend',
    //     '<div class="lds-ellipsis client-box-del-loader"><div></div><div></div><div></div><div></div></div>');
    document.querySelector('#client' + clientId).insertAdjacentHTML('afterend',
        '<span></span><span></span><span></span><span></span>');

    // Change loader size in changing the css variable
    document.documentElement.style.setProperty('--three-dots-loader-factor', '0.5');
    // Hide delete icon
    document.querySelector('.card-del-icon[data-id="' + clientId + '"]').style.display = 'none';
    // Lower client box opacity to reinforce feeling that something is happening
    document.getElementById('client' + clientId).style.opacity = '0.6';
}
