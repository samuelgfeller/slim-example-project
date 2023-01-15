import {getClientNoteLoadingPlaceholderHtml} from "./client-read-template-note.html.js?v=0.2.0";

/**
 * Display client note content placeholders
 * @param {string|null} wrapperId
 */
export function displayClientNoteLoadingPlaceholder(wrapperId = null) {
    let noteContainer = document.getElementById(wrapperId ?? 'client-note-wrapper');

    // Display as many content placeholders as there are notes
    let notesAmount = noteContainer.dataset.notesAmount;
    if (!notesAmount || notesAmount === '0' || notesAmount === '') {
        notesAmount = 3; // Default
    }
    for (let i = 0; i < notesAmount; i++) {
        noteContainer.insertAdjacentHTML('beforeend', getClientNoteLoadingPlaceholderHtml());
    }
}

/**
 * Remove placeholders
 * @param {string|null} wrapperId
 */
export function removeClientNoteContentPlaceholder(wrapperId = null) {
    let noteContainer = document.getElementById(wrapperId ?? 'client-note-wrapper');

    // I had a very strange issue. With getElementsByClassName I got 3 elements but only 2 seem to be looped through
    let contentPlaceholders = noteContainer.querySelectorAll('.client-note-loading-placeholder');
    // Foreach loop over content placeholders
    for (let contentPlaceholder of contentPlaceholders) {
        // remove from DOM
        contentPlaceholder.remove();
    }
}