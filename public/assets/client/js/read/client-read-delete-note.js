/**
 * Make database call to delete note
 *
 * @param noteId
 * @param noteContainer
 */
import {basePath} from "../../../general/js/config.js";

/**
 * Make delete request to db
 * @param noteId numeric id
 * @param noteContainer
 */
export function deleteNoteRequestToDb(noteId, noteContainer) {
    // Show loader
    let circleLoader = document.getElementById('note'+noteId).parentNode.querySelector('.circle-loader');
    circleLoader.style.display = 'inline-block';

    // Make ajax call
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
                let textStatus = JSON.parse(xHttp.responseText).status;

                // Show checkmark only on status success and if user is not typing
                if (textStatus === 'success') {
                    noteContainer.remove();
                }
            }

        }
    }

    xHttp.open('DELETE', basePath + 'notes' + '/' + noteId, true);
    xHttp.setRequestHeader("Content-type", "application/json");

    xHttp.send();
}