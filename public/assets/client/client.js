// Global variables
// Get basepath. Especially useful when developing on localhost/project-name
const basePath = document.getElementsByTagName('base')[0].getAttribute('href');

// Load clients on
// loadClients();

// Open modal box to create new client after click on plus button
document.getElementById('create-client-btn').addEventListener('click', function () {
    createClientModal();
});

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
});

/**
 * Load clients into dom
 */
function loadClients() {
    displayClientContentPlaceholder();
    // 'own' if own clients should be loaded after creation or 'all' if all should
    let clientVisibilityScope = document.getElementById('client-wrapper').dataset.dataClientFilter;
    let queryParams = clientVisibilityScope === 'own' ? '?user=session' : '';

    let xHttp = new XMLHttpRequest();
    xHttp.onreadystatechange = function () {
        if (xHttp.readyState === XMLHttpRequest.DONE) {
            // Fail
            if (xHttp.status !== 200) {
                // Default fail handler
                handleFail(xHttp);
            }
            // If status code 401 user is not logged in
            if (xHttp.status === 401) {
                removeContentPlaceholder();
                document.getElementById('client-wrapper').insertAdjacentHTML('afterend',
                    '<p>Please <a href="' + JSON.parse(xHttp.responseText).loginUrl +
                    '">login</a> to access clients assigned to you.</p>');
            }
            // Success
            else {
                let clients = JSON.parse(xHttp.responseText);
                removeContentPlaceholder();
                addClientsToDom(clients);
            }
        }
    };

    // For GET requests, query params have to be passed in the url directly. They are ignored in send()
    xHttp.open('GET', basePath + 'clients' + queryParams, true);
    xHttp.setRequestHeader("Content-type", "application/json");

    xHttp.send();
}

/**
 * Display client content placeholders
 */
function displayClientContentPlaceholder() {
    let clientWrapper = document.getElementById('client-wrapper');
    // Empty clients
    clientWrapper.innerHTML = '';

    let contentPlaceholderHtml =
        '<div class="preloading-card">' +
        '    <div class="preloading-card-header">' +
        '        <div class="load-wrapper">' +
        '            <div class="activity"></div>' +
        '        </div>' +
        '    </div>' +
        '    <div class="preloading-card-body">' +
        '        <div class="load-wrapper">' +
        '            <div class="activity"></div>' +
        '        </div>' +
        '    </div>' +
        '</div>';

    // Add content placeholder 3 times
    clientWrapper.insertAdjacentHTML('beforeend', contentPlaceholderHtml);
    clientWrapper.insertAdjacentHTML('beforeend', contentPlaceholderHtml);
    clientWrapper.insertAdjacentHTML('beforeend', contentPlaceholderHtml);
}

/**
 * Remove placeholders
 */
function removeContentPlaceholder() {
    // I had a very strange issue. With getElementsByClassName I got 3 elements but only 2 seem to be looped through
    let contentPlaceholders = document.querySelectorAll('.preloading-card');
    // Foreach loop over content placeholders
    for (let contentPlaceholder of contentPlaceholders) {
        // remove from DOM
        contentPlaceholder.remove();
    }
}

/**
 * Add client to page
 *
 * @param {object[]} clients
 */
function addClientsToDom(clients) {
    let clientContainer = document.getElementById('client-wrapper');

    // If no results, tell user so
    if (clients.length === 0) {
        clientContainer.insertAdjacentHTML('afterend', '<p>No clients were found.</p>')
    }

    // Loop over clients and add to DOM
    for (const client of clients) {
        // Client card HTML
        let clientHtml = '<div class="client-card" id="client' + client.clientId + '">' +
            '        <h3 class="card-header">' + client.first_name + ' ' + client.last_name + '</h3>' +
            '        <div id="card-content' + client.id + '">' +
            '            <p class="display-newlines"><b>' + client.age + '</b></p>' +
            '            <p class="client-card-additional-info">Updated: ' +
            '               <span class="layout-color-text">' + client.updated_at.date + '</span><br>' +
            '            User: <span class="layout-color-text">' + client.userData.firstName + ' ' + client.userData.surname + '</span></p>' +
            '        </div>' +
            '</div>';

        // Add to DOM
        clientContainer.insertAdjacentHTML('beforeend', clientHtml);
    }
}

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
