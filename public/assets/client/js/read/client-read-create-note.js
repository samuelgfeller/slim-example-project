import {basePath} from "../../../general/js/config.js";
import {
    hideCheckmarkLoader,
    initActivityTextareasEventListeners
} from "./client-read-text-area-event-listener-setup.js";

export function addNewNoteTextarea() {
    let container = document.querySelector('#client-activity-textarea-container');
    // Insert after end of activity header
    document.querySelector('#activity-header').insertAdjacentHTML('afterend', `<div>
                <label for=""
                       class="discrete-label textarea-label"></label>
                <!-- Extra div necessary to position circle loader to relative parent without taking label into account -->
                <div class="relative">
                    <!-- Textarea opening and closing has to be on the same line to prevent unnecessary line break -->
                    <textarea class="auto-resize-textarea" id=""
                              data-note-id="new-note"
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
}


export function insertNewNoteToDb(textarea) {
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
                    // Show checkmark in loader
                    circleLoader.classList.add('load-complete');
                    circleLoader.querySelector('.checkmark').style.display = 'block';

                    let noteId = response.data.noteId;
                    textarea.id = 'note' + noteId;
                    textarea.dataset.noteId = noteId;
                    // There are 2 parents before the label is a child
                    let label = textarea.parentNode.parentNode.querySelector('label.textarea-label');
                    label.setAttribute('for', noteId);
                    label.innerHTML = response.data.userFullName;

                    // Add note id to loader
                    textarea.parentNode.querySelector('.circle-loader').dataset.noteId = noteId;

                    // Remove checkmark after 1 sec
                    setTimeout(function () {
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
    };

    xHttp.open('POST', basePath + 'notes', true);
    xHttp.setRequestHeader("Content-type", "application/json");

    // Data format: "fname=Henry&lname=Ford"
    // In [square brackets] to be evaluated
    xHttp.send(JSON.stringify({
        [textarea.name]: textarea.value,
        // Not camelCase as html form names are underline too
        client_id: document.getElementById('client-id').value
    }));
}