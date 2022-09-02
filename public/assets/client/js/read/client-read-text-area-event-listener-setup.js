import {saveNoteChangeToDb} from "./client-read-save-existing-note.js";
import {insertNewNoteToDb} from "./client-read-create-note.js";


// To display the checkmark loader only when the user expects that his content is saved we have to know if he/she is
// still typing. Otherwise, the callback of the "old" ajax request to save shows the checkmark loader when it's done
export let userIsTyping = false; // Has to be outside function to export and import properly
// To change this variable from another file, a function has to be created to modify it https://stackoverflow.com/a/53723394/9013718
export function changeUserIsTyping(value) {
    userIsTyping = value;

}
/**
 * Activity textareas are editable on click and auto save on input pause
 */

export function initActivityTextareasEventListeners() {
    let activityTextareas = document.querySelectorAll(
        '#client-activity-textarea-container textarea, #main-note-textarea-div textarea'
    );

    let textareaInputPauseTimeoutId;
    for (let textarea of activityTextareas) {
        textarea.addEventListener('focus', function (e) {
            this.removeAttribute('readonly');
        });
        textarea.addEventListener('input', function () {
            userIsTyping = true;
            // Hide loader if there was one
            hideCheckmarkLoader(this.parentNode.querySelector('.circle-loader'));
            // Only save if 1 second writing pause
            clearTimeout(textareaInputPauseTimeoutId);
            textareaInputPauseTimeoutId = setTimeout(function () {
                // Runs 1 second after the last change
                let noteId = textarea.dataset.noteId;
                if (noteId !== 'new-note') {
                    saveNoteChangeToDb.call(textarea, noteId);
                }else{
                    insertNewNoteToDb(textarea);
                }
            }, 1000);
        });
        // textarea.addEventListener('change', saveNoteChangeToDb, false)
    }
}

export function hideCheckmarkLoader(checkmarkLoader) {
    checkmarkLoader.classList.remove('load-complete');
    checkmarkLoader.querySelector('.checkmark').style.display = 'none';
    checkmarkLoader.style.display = 'none';
}
