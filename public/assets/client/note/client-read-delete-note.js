/**
 * Make ajax call to delete note from database
 *
 * @param noteId
 * @param noteContainer
 */
import {hideCheckmarkLoader} from "./client-read-note-event-listener-setup.js?v=0.4.0";
import {submitDelete} from "../../general/ajax/submit-delete-request.js?v=0.4.0";

/**
 * Make ajax delete request
 *
 * @param noteId numeric id
 * @param noteContainer
 */
export function makeDeleteNoteRequest(noteId, noteContainer) {
    // Find the correct note inside note container
    let textarea = noteContainer.querySelector('textarea');
    // Show loader
    let circleLoader = textarea.parentNode.querySelector('.circle-loader');
    circleLoader.style.display = 'inline-block';
    // Dim note to indicate that it is being deleted
    noteContainer.style.opacity = '0.5';

    // Make delete request
    let clientId = document.getElementById('client-id').value;

    submitDelete('notes/' + noteId, "clients/" + clientId)
    .then(jsonResponse => {
        if (jsonResponse && jsonResponse.status === 'success') {
            noteContainer.remove();
        }
    })
    .catch(error => {
        // Fail
        hideCheckmarkLoader(circleLoader, 'Delete note fail');
        noteContainer.style.opacity = '1';
        console.error('Error:', error);
    });
}