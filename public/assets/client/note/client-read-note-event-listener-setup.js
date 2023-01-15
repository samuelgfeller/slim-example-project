import {disableHideCheckMarkTimeoutOnUpdate, saveNoteChangeToDb} from "./client-read-save-existing-note.js?v=0.2.0";
import {disableHideCheckMarkTimeoutOnCreation, insertNewNoteToDb} from "./client-read-create-note.js?v=0.2.0";
import {deleteNoteRequestToDb} from "./client-read-delete-note.js?v=0.2.0";
import {createAlertModal} from "../../general/page-component/modal/alert-modal.js?v=0.2.0";
import {submitUpdate} from "../../general/ajax/submit-update-data.js?v=0.2.0";


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
        '.client-note-wrapper textarea, #main-note-textarea-div textarea'
    );
    initAllButtonsAboveNotesEventListeners();
    for (let textarea of clientReadTextareas) {
        // Called when new note is created as well
        toggleReadOnlyAndBtnAboveNote(textarea);

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
            // console.log(textarea.checkValidity());
            if (textarea.checkValidity() !== false) {
                if (noteId === 'new-note') {
                    insertNewNoteToDb(textarea);
                } else if (noteId === 'new-main-note') {
                    insertNewNoteToDb(textarea, true)
                } else {
                    // Call function to save note passing textarea as "this" and noteId as argument
                    saveNoteChangeToDb.call(textarea, noteId);
                }
            } else {
                textarea.reportValidity();
            }
        }, 1000);
    });
}

export function toggleReadOnlyAndBtnAboveNote(textarea) {
    // Del btn and removing readonly only matters if textarea can be edited
    // console.log(textarea.dataset.editable === '1');
    if (textarea.dataset.editable === '1') {
        // Get delete btn with note label to show it on textarea focus
        let buttonsAboveNote = [];
        if (!textarea.classList.contains('main-textarea') && textarea.id !== 'new-note') {
            buttonsAboveNote = document.querySelectorAll(`label[data-note-id="${textarea.dataset.noteId}"] .btn-above-note`);
        }

        const showHideButtonsAboveNote = (hide = false) => {
            // Show buttons above note on focus independent of hover state
            for (const btn of buttonsAboveNote) {
                if (hide === true && // If hide button, only hide if note container not hidden
                    (!btn.classList.contains('hide-note-btn') ||
                        !btn.closest('.note-container').classList.contains('hidden-note'))
                ) {
                    btn.style.display = null;
                } else {
                    btn.style.display = 'inline-block';
                }
            }
        }

        textarea.addEventListener('focus', function (e) {
            this.removeAttribute('readonly');
            showHideButtonsAboveNote();
        });
        textarea.addEventListener('focusout', function (e) {
            this.setAttribute('readonly', 'readonly');
            // Remove display property from element to make it show on hover (default css behaviour)
            showHideButtonsAboveNote(true)
        });

        // Display if already focus as the focus event listener is not triggered when new note is created; there is already focus before
        if (document.activeElement === textarea) {
            showHideButtonsAboveNote();
        }
    }
}


export function initAllButtonsAboveNotesEventListeners() {
    const deleteNoteButtons = document.querySelectorAll('.delete-note-btn');
    for (const deleteNoteBtn of deleteNoteButtons) {
        // In separate function as this one is called once on page load and then the one blow on note creation too
        addDeleteNoteBtnEventListener(deleteNoteBtn);
    }
    const hideNoteButtons = document.querySelectorAll('.hide-note-btn:not(.not-clickable)');
    for (const hideNoteBtn of hideNoteButtons) {
        addHideNoteBtnEventListener(hideNoteBtn);
    }

}

/**
 * Adds event listener to del btn to open modal box and delete note on confirmation
 * In own function as event listener has to be initialized once on notes load
 * and then also on note creation.
 *
 * @param deleteNoteBtn
 */
export function addDeleteNoteBtnEventListener(deleteNoteBtn) {
    deleteNoteBtn.addEventListener('click', () => {
        let noteId = deleteNoteBtn.closest('label').dataset.noteId;
        let title = 'Are you sure that you want to delete this note?';
        let info = 'Once the note is deleted, it can only be recovered by a database administrator.';
        createAlertModal(title, '', () => {
            deleteNoteRequestToDb(noteId, document.getElementById(
                'note-' + noteId + '-container'
            ));
        });
    });
}

/**
 * Adds event listener to hide btn
 * In own function as event listener has to be initialized once on notes load
 * and then also on note creation.
 *
 * @param btn
 */
export function addHideNoteBtnEventListener(btn) {
    btn.addEventListener('click', () => {
        const noteContainer = btn.closest('.note-container');
        const noteId = btn.closest('label').dataset.noteId;
        let newHiddenValue = 0;
        // Toggle hidden note
        const toggleEyeIcon = () => {
            if (noteContainer.classList.contains('hidden-note')) {
                newHiddenValue = 0;
                // If note was already hidden, it has to be changed to 0
                noteContainer.classList.remove('hidden-note');
                // Only reset display if not focus on textarea
                if (document.activeElement !== noteContainer.querySelector('textarea')) {
                    btn.style.display = null;
                }
                btn.src = 'assets/general/general-img/eye-icon.svg';
            } else {
                // If note not already hidden, it has to be changed to 1
                newHiddenValue = 1;
                btn.style.display = 'inline-block';
                noteContainer.classList.add('hidden-note');
                btn.src = 'assets/general/general-img/eye-icon-active.svg';
            }
        }
        // Toggle eye icon as soon as user clicks even before request
        toggleEyeIcon();
        // Submit update to server
        submitUpdate(
            {hidden: newHiddenValue}, `notes/${noteId}`, `notes/${noteId}`
        ).then(r => {}).catch(r => {
            // Revert eye to how it was before on failure
            toggleEyeIcon();
        });
    });
}



export function hideCheckmarkLoader(checkmarkLoader, origin) {
    // console.log(origin);
    checkmarkLoader.classList.remove('load-complete');
    checkmarkLoader.querySelector('.checkmark').style.display = 'none';
    checkmarkLoader.style.display = 'none';
}
