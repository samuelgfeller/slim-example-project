/**
 * Create and display modal box to create a new client
 */
export function displayClientCreateModal() {
    let header = '<h2>Create client</h2>';
    let body = `<div class="modal-form">
<form action="javascript:void(0);" class="wide-modal-form" id="create-client-modal-form">
        <div class="wide-modal-form-input-group">
            <label for="first-name-input">First name</label>
            <input type="text" name="first_name" id="first-name-input" placeholder="Hans" class="form-input" 
            minlength="2" maxlength="100">
        </div>
        <div class="wide-modal-form-input-group">
            <label for="last-name-input">Last name</label>
            <input type="text" name="last_name" id="last-name-input" placeholder="Zimmer" class="form-input" 
            minlength="2" maxlength="100">
        </div>
        <div class="wide-modal-form-input-group">
            <label for="birthdate-input">Birthdate</label>
            <input type="date" name="birthdate" id="birthdate-input" placeholder="15.03.2000" class="form-input">
        </div>
        <div class="wide-modal-form-input-group">
            <label for="location-input">Location</label>
            <input type="text" placeholder="Basel" id="location-input" name="location" class="form-input" minlength="2" 
            maxlength="100">
        </div>
        <div class="wide-modal-form-input-group double-width-modal-form-input-group">
            <label for="create-message-textarea" class="form-label">Main note</label>
            <!-- Name has to be "message" as it's the name used in note validation -->
            <textarea rows="4" cols="50" name="message" id="create-message-textarea" class="form-input"
                      placeholder="Your message here." minlength="0" maxlength="500"></textarea>
        </div>
        <div class="wide-modal-form-input-group" id="client-sex-input-group-div">
            <label>Sex</label><br>
            <!-- Sex radio buttons are added after modal load in client-template-util.js  -->
        </div>
        <div class="wide-modal-form-input-group">
            <label for="phone-input">Phone number</label>
            <input type="text" name="phone" id="phone-input" placeholder="061 422 32 11" class="form-input" 
            minlength="3" maxlength="20">
        </div>
        <div class="wide-modal-form-input-group">
            <label for="email-input">E-Mail</label>
            <input type="text" name="email" id="email-input" placeholder="mail@example.com" class="form-input" 
            maxlength="254">
        </div>
        <div class="wide-modal-form-input-group">
            <label for="assigned-user-select">Assigned user</label>
            <select name="user_id" class="form-select" id="assigned-user-select">
                <!-- Dropdown options loaded afterwards -->
            </select>
        </div>
        <div class="wide-modal-form-input-group">
            <label for="client-status-select">Status</label>
            <select name="client_status_id" id="client-status-select" class="form-select">
            <!-- Dropdown options loaded afterwards -->
            </select>
        </div>
    </div>`;
    let footer = `<button type="button" id="client-create-submit-btn" class="submit-btn modal-submit-btn">Create client
    </button></form>
    <div class="clearfix">
    </div>`;
    document.getElementById('client-wrapper').insertAdjacentHTML('afterend', '<div id="create-client-div"></div>');
    let container = document.getElementById('create-client-div');
    createModal(header, body, footer, container);
}
