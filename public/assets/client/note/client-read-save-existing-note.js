import {basePath} from "../../general/general-js/config.js?v=0.4.1";
import {
    changeUserIsTyping,
    hideCheckmarkLoader,
    userIsTypingOnNoteId
} from "./client-read-note-event-listener-setup.js?v=0.4.1";
import {handleFail} from "../../general/ajax/ajax-util/fail-handler.js?v=0.4.1";

let noteSaveHideCheckMarkTimeout = [];

/**
 * When a note is saved, the checkmark loader is displayed.
 * Three seconds later, it's hidden, but this should not happen if the user typed in the
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

    // The textarea id is needed in the fetch call but "this" is the textarea inside the call
    let textareaId = this.id;

    // Data format: "firstname=Henry&lastname=Ford"
    // In [square brackets] to be evaluated
    let body = JSON.stringify({
        [this.name]: this.value,
        // is_main: this.classList.contains('main-note-textarea') ? 1 : 0,
    }); // "this" is the textarea

    fetch(basePath + 'notes' + '/' + noteId, {
        method: 'PUT',
        headers: {"Content-type": "application/json"},
        body: body
    })
        .then(async response => {
            if (!response.ok) {
                // Default fail handler
                await handleFail(response, textareaId);
                hideCheckmarkLoader(circleLoader, 'Save existing fail');
                // Throw error so it can be caught in catch block
                throw new Error('Response status: ' + response.status);
            }
            let textStatus = (await response.json()).status;
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
                        hideCheckmarkLoader(circleLoader, '3s after successful save');
                    }, 3000);
                } else {
                    // Hide checkmark loader "cleanly" so that it's not broken on the next input
                    hideCheckmarkLoader(circleLoader, 'Non success note save');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}