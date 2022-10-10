import {disableHideCheckMarkTimeoutOnUpdate, saveNoteChangeToDb} from "./client-read-save-existing-note.js";
import {disableHideCheckMarkTimeoutOnCreation, insertNewNoteToDb} from "./client-read-create-note.js";
import {deleteNoteRequestToDb} from "./client-read-delete-note.js";
import {createAlertModal} from "../../../general/js/alert-modal.js";


// To display the checkmark loader only when the user expects that his content is saved we have to know if he/she is
// still typing. Otherwise, the callback of the "old" ajax request to save, shows the checkmark loader when it's done
// If the user starts typing on another note, the checkmark loader on the note before should still appear hence on note id
export let userIsTypingOnNoteId = false; // Has to be outside function to export and import properly
// To change this variable from another file, a function has to be created to modify it https://stackoverflow.com/a/53723394/9013718
export function changeUserIsTyping(value) {
    userIsTypingOnNoteId = value;
}

/**
 * Activity textareas are editable on click and auto save on input pause
 * This function is called each time after adding new note
 */
export function initNotesEventListeners() {

    // Target all textareas including main note
    let clientReadTextareas = document.querySelectorAll(
        '#client-activity-textarea-container textarea, #main-note-textarea-div textarea'
    );
    console.log(clientReadTextareas);

    for (let textarea of clientReadTextareas) {
        // Called when new note is created as well
        toggleTextareaReadOnlyAndAddDeleteBtnDisplay(textarea);

        // On init, add read-only attribute to each textarea with js so that if user clicks on a textarea before
        // full page load, he can continue to write
        if (document.activeElement !== textarea) {
            textarea.setAttribute('readonly', 'readonly');
        }


        // In own function to be able to be called individually for new note to prevent that they have duplicate event listeners
        // Which happened when I called initActivityTextareasEventListeners after adding new textarea and update request was fired twice
        addTextareaInputEventListener(textarea);

        // textarea.addEventListener('change', saveNoteChangeToDb, false)
    }
}

let textareaInputPauseTimeoutId;

export function addTextareaInputEventListener(textarea) {
    let noteId = textarea.dataset.noteId;

    // Remove focus when ctrl + enter is pressed
    textarea.addEventListener('keypress', function (e) {
        if ((e.ctrlKey || e.metaKey) && (e.keyCode === 13 || e.keyCode === 10)) {
            // Focus out
            textarea.blur();
            // Ideally it would save directly to give the user the feedback but that adds quit a bit of complexity
            // especially with loaders and hiding them (or NOT hiding them if ctrl + enter is pressed right after an
            // input pause and this level of optimisation is not needed for this project
        }
    });
    textarea.addEventListener('input', function () {
        userIsTypingOnNoteId = noteId;
        // Hide loader if there was one
        hideCheckmarkLoader(textarea.parentNode.querySelector('.circle-loader'), 'New input');
        // Clear timeout that hides it after note update or creation if there was one
        disableHideCheckMarkTimeoutOnUpdate(noteId);
        disableHideCheckMarkTimeoutOnCreation(noteId);
        // Only save if 1 second writing pause
        clearTimeout(textareaInputPauseTimeoutId);
        textareaInputPauseTimeoutId = setTimeout(function () {
            // Runs 1 second after the last change
            if (textarea.checkValidity() !== false && textarea.value.length > 0) {
                if (noteId === 'new-note') {
                    insertNewNoteToDb(textarea);
                } else if (noteId === 'new-main-note') {
                    insertNewNoteToDb(textarea, true)
                } else {
                    saveNoteChangeToDb.call(textarea, noteId);
                }
            } else {
                textarea.reportValidity();
            }
        }, 1000);
    });
}

export function toggleTextareaReadOnlyAndAddDeleteBtnDisplay(textarea) {
    // Del btn and removing readonly only matters if textarea can be edited
    console.log(textarea.dataset.editable === '1');
    if (textarea.dataset.editable === '1') {
        // Get delete btn with note label to show it on textarea focus
        let delBtn = null;
        if (!textarea.classList.contains('main-textarea') && textarea.id !== 'new-note') {
            delBtn = document.querySelector('.delete-note-btn[data-note-id="' + textarea.dataset.noteId + '"]');
        }

        textarea.addEventListener('focus', function (e) {
            this.removeAttribute('readonly');
            // let delBtn = findDOMDelBtnWithTextarea(textarea);
            if (delBtn !== null) {
                delBtn.style.display = 'inline-block';
            }
        });
        textarea.addEventListener('focusout', function (e) {
            this.setAttribute('readonly', 'readonly');
            // let delBtn = findDOMDelBtnWithTextarea(textarea);
            if (delBtn !== null) {
                // Remove display property from element to make it show on hover (default css behaviour)
                delBtn.style.display = null;
            }
        });

        // Display if already focus as the focus event listener is not triggered when new note is created as there is already focus before
        if (document.activeElement === textarea) {
            if (delBtn !== null) {
                delBtn.style.display = 'inline-block';
            }
        }
    }
}

export function initAllDeleteBtnEventListeners() {
    let deleteNoteButtons = document.querySelectorAll('.delete-note-btn');
    for (const deleteNoteBtn of deleteNoteButtons) {
        // In separate function as this one is called once on page load and then the single one on each creation
        addDeleteNoteBtnEventListener(deleteNoteBtn);
    }
}

/**
 * Adds event listener to del btn to open modal box and delete note on confirmation
 *
 * @param deleteNoteBtn
 */
export function addDeleteNoteBtnEventListener(deleteNoteBtn) {
    deleteNoteBtn.addEventListener('click', () => {
        let noteId = deleteNoteBtn.dataset.noteId;
        let title = 'Are you sure that you want to delete this note?';
        let info = 'Once the note is deleted, it can only be recovered by a database administrator.';
        createAlertModal(title, '', () => {
            deleteNoteRequestToDb(noteId, document.getElementById(
                'note' + noteId + '-container'
            ));
        });
    });
}

export function hideCheckmarkLoader(checkmarkLoader, origin) {
    console.log(origin);
    checkmarkLoader.classList.remove('load-complete');
    checkmarkLoader.querySelector('.checkmark').style.display = 'none';
    checkmarkLoader.style.display = 'none';
}
