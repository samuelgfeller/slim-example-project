import {basePath} from "../../general/general-js/config.js?v=0.2.0";
import {
    addDeleteNoteBtnEventListener,
    addHideNoteBtnEventListener,
    addTextareaInputEventListener,
    hideCheckmarkLoader,
    toggleReadOnlyAndBtnAboveNote
} from "./client-read-note-event-listener-setup.js?v=0.2.0";
import {handleFail, removeValidationErrorMessages} from "../../general/ajax/ajax-util/fail-handler.js?v=0.2.0";
import {initAutoResizingTextareas} from "../../general/page-component/textarea/auto-resizing-textarea.js?v=0.2.0";

let noteCreationHideCheckMarkTimeout = [];

/**
 * Clears the timeout of hiding the checkmark loader after note creation
 * Same function also exists on save of existing note.
 * I don't fully understand why but if I don't check the note id here as well, it makes
 */
export function disableHideCheckMarkTimeoutOnCreation(noteId) {
    // parseInt necessary here as one is an int and the other a string
    if (parseInt(noteCreationHideCheckMarkTimeout['noteId']) === parseInt(noteId)) {
        // Clear the timeout that hides the loader 3s after creation (in case it re-appears after new input pause before 3s)
        clearTimeout(noteCreationHideCheckMarkTimeout['timeoutId']);
    }
}

export function addNewNoteTextarea() {
    // Hide no note info if there is one
    let noNotesInfo = document.getElementById('no-notes-info');
    if (noNotesInfo !== null) {
        noNotesInfo.remove();
    }
    // Check if bubble already exists and only create new one if there isn't one already
    let existingNewNoteBubble = document.getElementById('new-note');
    if (existingNewNoteBubble === null) {
        // Insert after end of activity header and not container as header comes as first element
        document.querySelector('#activity-header').insertAdjacentHTML('afterend', `<div class="note-container">
                <label for="new-note" class="bigger-select-label textarea-label" data-note-id="">
                       <a class="note-left-side-label no-style-a"></a>
                       <img class="btn-above-note hide-note-btn" alt="hide" src="assets/general/general-img/eye-icon.svg"
                       style="display: none">
                       <img class="btn-above-note delete-note-btn" alt="delete" src="assets/general/general-img/del-icon.svg" 
                       style="display: none">
                       <span
                            class="discrete-text note-right-side-label-span"></span></label>
                <!-- Extra div necessary to position circle loader to relative parent without taking label into account -->
                <div class="relative">
                    <!-- Textarea opening and closing has to be on the same line to prevent unnecessary line break -->
                    <textarea class="auto-resize-textarea" id="new-note"
                              data-note-id="new-note" minlength="4" maxlength="1000"
                              name="message"></textarea>
                    <div class="circle-loader client-note" data-note-id="">
                        <div class="checkmark draw"></div>
                    </div>
                </div>
            </div>`);
        let textarea = document.getElementById('new-note');
        // let textarea = document.querySelector('#client-note-wrapper textarea:first-of-type');
        // Refresh all activity textareas event listeners to count new ones in too didn't work as it created
        // duplicate events like saving but this simply adds event listener to targets textarea
        addTextareaInputEventListener(textarea);
        // Make that newly created textarea resize automatically as well
        initAutoResizingTextareas();

        textarea.addEventListener('focusout', removeNewNoteTextareaIfEmpty);
        // Has to be after textarea event listener init
        textarea.focus();
    } else {
        existingNewNoteBubble.focus();
    }
}

function removeNewNoteTextareaIfEmpty() {
    // "this" is the textarea
    if (this.value === '') {
        this.remove();
    }
}

export function insertNewNoteToDb(textarea, isMainNote = false) {
    // By using querySelector on the targeted textarea parent it's certain that the right circleLoader is targeted
    let circleLoader = textarea.parentNode.querySelector('.circle-loader');
    circleLoader.style.display = 'inline-block';

    textarea.removeEventListener('focusout', removeNewNoteTextareaIfEmpty);

    // Make ajax call
    let xHttp = new XMLHttpRequest();
    xHttp.onreadystatechange = function () {
        if (xHttp.readyState === XMLHttpRequest.DONE) {
            // Fail
            if (xHttp.status !== 201 && xHttp.status !== 200) {
                // Default fail handler
                handleFail(xHttp, textarea.id);
                hideCheckmarkLoader(circleLoader, 'create new note fail');
            }
            // Success
            else {
                removeValidationErrorMessages();
                let response = JSON.parse(xHttp.responseText);
                // Here it's not important to check if user is still typing as new note can be saved as soon as possible
                // Show checkmark only on status success and if user is not typing
                if (response.status === 'success') {
                    populateNewNoteDomAttributes(textarea, response.data);
                } else {
                    // Hide checkmark loader "cleanly" so that it's not broken on the next input
                    hideCheckmarkLoader(circleLoader, 'Client create note after non success creation');
                }
            }
        }
    };

    xHttp.open('POST', basePath + 'notes', true);
    xHttp.setRequestHeader("Content-type", "application/json");
    // Important to add content type json and "Redirect-to-route-name-if-unauthorized" header for the UserAuthenticationMiddleware
    // to know to send the login url in the json response body and where to redirect back after a successful login
    xHttp.setRequestHeader("Redirect-to-url-if-unauthorized", basePath + "client/" +
        document.getElementById('client-id').value);

    // Data format: "fname=Henry&lname=Ford"
    // In [square brackets] to be evaluated
    xHttp.send(JSON.stringify({
        [textarea.name]: textarea.value,
        // Not camelCase as html form names are underline too
        client_id: document.getElementById('client-id').value,
        is_main: isMainNote ? 1 : 0,
    }));
}

/**
 * Add the html attributes to note container and children
 * so that they interact well like the preloaded ones
 *
 * @param textarea
 * @param responseData data that the served passed in http response
 */
function populateNewNoteDomAttributes(textarea, responseData) {
    // Target container
    let noteContainer = textarea.closest('.note-container');

    // Show checkmark in loader
    let circleLoader = textarea.parentNode.querySelector('.circle-loader');
    circleLoader.classList.add('load-complete');
    circleLoader.querySelector('.checkmark').style.display = 'block';

    let noteId = responseData.noteId;
    textarea.id = 'note-' + noteId;
    textarea.dataset.noteId = noteId;
    // If noteContainer is null it means that it's the main textarea which doesn't have a label
    if (noteContainer !== null) {
        // There are 2 parents before the label is a child
        let label = noteContainer.querySelector('label.textarea-label');
        label.setAttribute('for', textarea.id);
        label.querySelector('.note-left-side-label').innerHTML = responseData.createdDateFormatted;
        label.querySelector('.note-left-side-label').href = `${window.location.href}#note-${noteId}-container"`;
        label.dataset.noteId = noteId;
        // Show buttons above note (default css behaviour)
        const buttonsAboveNote = label.querySelectorAll(`.btn-above-note`);
        for (const btn of buttonsAboveNote) {
            btn.style.display = null;
        }
        // Add note author
        label.querySelector('.note-right-side-label-span').innerHTML = responseData.userFullName;
        // Add container id
        noteContainer.id = 'note-' + noteId + '-container';
        // Make delete button work
        addDeleteNoteBtnEventListener(label.querySelector(`.delete-note-btn`));
        addHideNoteBtnEventListener(label.querySelector(`.hide-note-btn`));
    }
    // Add note id to loader
    textarea.parentNode.querySelector('.circle-loader').dataset.noteId = noteId;

    // Add the read only event listener
    toggleReadOnlyAndBtnAboveNote(textarea);

    noteCreationHideCheckMarkTimeout['noteId'] = noteId;
    // Remove checkmark after x sec
    noteCreationHideCheckMarkTimeout['timeoutId'] = setTimeout(function () {
        // Hide circle loader and its child the checkmark
        // circleLoader.style.animation = 'loader-spin 1.2s infinite linear';
        hideCheckmarkLoader(circleLoader, '3s after note creation');
    }, 3000);
}