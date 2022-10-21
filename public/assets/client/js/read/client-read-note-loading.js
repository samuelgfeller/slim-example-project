import {basePath} from "../../../general/js/config.js";
import {getNoteHtml} from "./client-read-template-note.html.js";
import {
    displayClientNoteLoadingPlaceholder,
    removeClientNoteContentPlaceholder
} from "./client-read-note-loading-placeholder.js";

/**
 * Loading notes into dom
 */
export function loadClientNotes(callbackFunction) {
    displayClientNoteLoadingPlaceholder();

    let clientId = document.getElementById('client-id').value;
    let queryParams = 'client_id=' + clientId;

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
                // removeClientCardContentPlaceholder();
                document.getElementById('client-wrapper').insertAdjacentHTML('afterend',
                    '<p>Please <a href="' + JSON.parse(xHttp.responseText).loginUrl +
                    '">login</a> to access clients assigned to you.</p>');
            }
            // Success
            else {
                let parsedResponse = JSON.parse(xHttp.responseText);
                removeClientNoteContentPlaceholder();
                addNotesToDom(parsedResponse);
                callbackFunction();
            }
        }
    };

    // For GET requests, query params have to be passed in the url directly. They are ignored in send()
    xHttp.open('GET', basePath + 'notes?' + queryParams, true);

    xHttp.setRequestHeader("Content-type", "application/json");
    // Important to add content type json and "Redirect-to-route-name-if-unauthorized" header for the UserAuthenticationMiddleware
    // to know to send the login url in the json response body and where to redirect back after a successful login
    xHttp.setRequestHeader("Redirect-to-url-if-unauthorized", basePath + "client/" + clientId);


    xHttp.send();
}


/**
 * Add note to page
 *
 * @param {object[]} notes
 */
function addNotesToDom(notes) {
    let noteContainer = document.getElementById('client-activity-textarea-container');

    // If no results, tell user so
    if (notes.length === 0) {
        noteContainer.insertAdjacentHTML('afterend', '<p id="no-notes-info">No notes were found.</p>')
    }


    // Loop over notes and add to DOM
    for (const note of notes) {
        // Client card HTML
        let noteHtml = getNoteHtml(
            note.noteId, note.noteCreatedAt, note.mutationRights, note.userFullName, note.noteMessage
        );

        // Add to DOM
        noteContainer.insertAdjacentHTML('beforeend', noteHtml);
    }
}