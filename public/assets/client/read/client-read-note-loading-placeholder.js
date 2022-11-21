import {getClientNoteLoadingPlaceholderHtml} from "./client-read-template-note.html.js?v=0.1";

/**
 * Display client note content placeholders
 */
export function displayClientNoteLoadingPlaceholder() {
    let noteContainer = document.getElementById('client-activity-textarea-container');

    // Display as many content placeholders as there are notes
    let notesAmount = noteContainer.dataset.notesAmount;
    if (notesAmount === '0' || notesAmount === ''){
        notesAmount = 3; // Default
    }
    for (let i = 0; i < notesAmount; i++) {
        noteContainer.insertAdjacentHTML('beforeend', getClientNoteLoadingPlaceholderHtml());
    }
}

/**
 * Remove placeholders
 */
export function removeClientNoteContentPlaceholder() {
    // I had a very strange issue. With getElementsByClassName I got 3 elements but only 2 seem to be looped through
    let contentPlaceholders = document.querySelectorAll('.client-note-loading-placeholder');
    // Foreach loop over content placeholders
    for (let contentPlaceholder of contentPlaceholders) {
        // remove from DOM
        contentPlaceholder.remove();
    }
}