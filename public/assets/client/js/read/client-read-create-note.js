import {basePath} from "../../../general/js/config.js";
import {
    addDeleteNoteBtnEventListener,
    hideCheckmarkLoader,
    initActivityTextareasEventListeners, toggleTextareaReadOnlyAndAddDeleteBtnDisplay
} from "./client-read-text-area-event-listener-setup.js";

let noteCreationHideCheckMarkTimeout;

/**
 * Clears the timeout of hiding the checkmark loader after note creation
 * Same function also exists on save of existing note
 */
export function disableHideCheckMarkTimeoutOnCreation() {
    clearTimeout(noteCreationHideCheckMarkTimeout);
}

export function addNewNoteTextarea() {
    // Check if bubble already exists and only create new one if there isn't one already
    let existingNewNoteBubble = document.getElementById('new-note');
    if (existingNewNoteBubble === null) {
        // Insert after end of activity header and not container as header comes as first element
        document.querySelector('#activity-header').insertAdjacentHTML('afterend', `<div class="note-container">
                <label for="new-note"
                       class="discrete-label textarea-label">
                       <span class="label-user-full-name"></span>
                       <img class="delete-note-btn" alt="delete" src="assets/general/img/del-icon.svg" data-note-id=""
                       style="display: none">
                       <span
                            class="discrete-text note-created-date"></span></label>
                <!-- Extra div necessary to position circle loader to relative parent without taking label into account -->
                <div class="relative">
                    <!-- Textarea opening and closing has to be on the same line to prevent unnecessary line break -->
                    <textarea class="auto-resize-textarea" id="new-note"
                              data-note-id="new-note" minlength="4" maxlength="500"
                              readonly="readonly" name="message"></textarea>
                    <div class="circle-loader client-read" data-note-id="">
                        <div class="checkmark draw"></div>
                    </div>
                </div>
            </div>`);
        // Refresh activity textareas event listeners to count new ones in too
        initActivityTextareasEventListeners();
        // Make that newly created textarea resize automatically as well
        initAutoResizingTextareas();

        // Has to be after textarea event listener init
        let textarea = document.querySelector('#client-activity-textarea-container textarea:first-of-type');
        textarea.focus();
    } else {
        existingNewNoteBubble.focus();
    }
}


export function insertNewNoteToDb(textarea, isMainNote = false) {
    // By using querySelector on the targeted textarea parent it's certain that the right circleLoader is targeted
    let circleLoader = textarea.parentNode.querySelector('.circle-loader');
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
                let response = JSON.parse(xHttp.responseText);
                // Here it's not important to check if user is still typing as new note can be saved as soon as possible
                // Show checkmark only on status success and if user is not typing
                if (response.status === 'success') {
                    populateNewNoteDomAttributes(textarea, response.data);
                } else {
                    // Hide checkmark loader "cleanly" so that it's not broken on the next input
                    hideCheckmarkLoader(circleLoader);
                }
            }
        }
    };

    xHttp.open('POST', basePath + 'notes', true);
    xHttp.setRequestHeader("Content-type", "application/json");

    // Data format: "fname=Henry&lname=Ford"
    // In [square brackets] to be evaluated
    xHttp.send(JSON.stringify({
        [textarea.name]: textarea.value,
        // Not camelCase as html form names are underline too
        client_id: document.getElementById('client-id').value,
        is_main_note: isMainNote ? 1 : 0,
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
    textarea.id = 'note' + noteId;
    textarea.dataset.noteId = noteId;
    // There are 2 parents before the label is a child
    let label = noteContainer.querySelector('label.textarea-label');
    label.setAttribute('for', textarea.id);
    label.querySelector('.label-user-full-name').innerHTML = responseData.userFullName;
    label.querySelector('.delete-note-btn').dataset.noteId = noteId;
    label.querySelector('.note-created-date').innerHTML = responseData.createdDateFormatted;

    // Add note id to loader
    textarea.parentNode.querySelector('.circle-loader').dataset.noteId = noteId;
    // Add the read only event listener and display del btn
    toggleTextareaReadOnlyAndAddDeleteBtnDisplay(textarea);
    // Make delete button work
    addDeleteNoteBtnEventListener(document.querySelector('.delete-note-btn[data-note-id="' + noteId + '"]'));
    // Add container id
    noteContainer.id = 'note' + noteId + '-container';

    // Remove checkmark after x sec
    noteCreationHideCheckMarkTimeout = setTimeout(function () {
        // Hide circle loader and its child the checkmark
        // circleLoader.style.animation = 'loader-spin 1.2s infinite linear';
        hideCheckmarkLoader(circleLoader,);
    }, 3000);
}