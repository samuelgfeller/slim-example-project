import {basePath} from "../../../general/js/config.js";
import {
    changeUserIsTyping,
    hideCheckmarkLoader,
    userIsTypingOnNoteId
} from "./client-read-text-area-event-listener-setup.js";

let noteSaveHideCheckMarkTimeout = [];

/**
 * When note is saved, the checkmark loader is displayed 3 seconds later
 * it gets hidden but this should not happen if the user typed in the
 * meantime as it could hide the checkmark loader that should be displayed
 * for the next save request.
 */
export function disableHideCheckMarkTimeoutOnUpdate(noteId) {
    if (parseInt(noteSaveHideCheckMarkTimeout['noteId']) === parseInt(noteId)) {
        clearTimeout(noteSaveHideCheckMarkTimeout['timeoutId']);
    }
}

/**
 * Save note changes to database
 *
 * @param noteId
 */
export function saveNoteChangeToDb(noteId) {
    // Setting the var to false, to compare it on success. If it is not false anymore, it means that the user typed
    changeUserIsTyping(false);
    // show circle loader
    // By using querySelector on the targeted textarea parent it's certain that the right circleLoader is targeted
    let circleLoader = this.parentNode.querySelector('.circle-loader');
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
                // Only show checkmark loader if user didn't type on the same note in the meantime
                if (userIsTypingOnNoteId === false || userIsTypingOnNoteId !== noteId) {
                    // Show checkmark only on status success and if user is not typing
                    if (textStatus === 'success') {
                        // Show checkmark in loader
                        circleLoader.classList.add('load-complete');
                        circleLoader.querySelector('.checkmark').style.display = 'block';

                        noteSaveHideCheckMarkTimeout['noteId'] = noteId;
                        // Remove checkmark after 1 sec
                        noteSaveHideCheckMarkTimeout['timeoutId'] = setTimeout(function () {
                            // Hide circle loader and its child the checkmark
                            // circleLoader.style.animation = 'loader-spin 1.2s infinite linear';
                            hideCheckmarkLoader(circleLoader);
                        }, 3000);
                    } else {
                        // Hide checkmark loader "cleanly" so that it's not broken on the next input
                        hideCheckmarkLoader(circleLoader);
                    }
                }
            }
        }
    };

    xHttp.open('PUT', basePath + 'notes' + '/' + noteId, true);
    xHttp.setRequestHeader("Content-type", "application/json");

    // Data format: "fname=Henry&lname=Ford"
    // In [square brackets] to be evaluated
    xHttp.send(JSON.stringify({[this.name]: this.value})); // this is the textarea
}